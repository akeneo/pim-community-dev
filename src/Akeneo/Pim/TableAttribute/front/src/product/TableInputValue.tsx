import React from 'react';
import {AddingValueIllustration, TableInput} from 'akeneo-design-system';
import {ColumnCode, SelectOption, SelectOptionCode, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, useTranslate, useUserContext, useRouter} from '@akeneo-pim-community/shared';
import {TableFooter} from './TableFooter';
import styled from 'styled-components';
import {TableValueWithId} from './TableFieldApp';
import {getSelectOption, getSelectOptions} from '../repositories/SelectOption';
import {TableInputSelect} from './TableInputSelect';
import {TableCell} from '../models/TableValue';
import {TableValueViolatedCell} from "../legacy/table-field";

const TABLE_VALUE_ITEMS_PER_PAGE = [10, 20, 50, 100];

const TableInputContainer = styled.div`
  width: 100%;
`;

const CenteredHelper = styled.div`
  text-align: center;
`;

type TableInputValueProps = {
  attributeCode: string;
  valueData: TableValueWithId;
  tableConfiguration: TableConfiguration;
  onChange: (tableValue: TableValueWithId) => void;
  searchText: string;
  violatedCells: TableValueViolatedCell[];
};

const TableInputValue: React.FC<TableInputValueProps> = ({
  attributeCode,
  valueData,
  tableConfiguration,
  onChange,
  searchText = '',
  violatedCells = [],
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useRouter();
  const [itemsPerPage, setItemsPerPage] = React.useState<number>(TABLE_VALUE_ITEMS_PER_PAGE[0]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const [selectOptionLabels, setSelectOptionLabels] = React.useState<{[key: string]: string}>({});
  const [options, setOptions] = React.useState<{[columnCode: string]: SelectOption[]}>({});
  const isSearching = searchText.trim() !== '';

  const handleChange = (uniqueId: string, columnCode: ColumnCode, cellValue: TableCell | undefined) => {
    const rowIndex = valueData.findIndex(row => row['unique id'] === uniqueId);
    if (rowIndex >= 0) {
      const row = valueData[rowIndex];
      if (typeof cellValue === 'undefined') {
        delete row[columnCode];
      } else {
        row[columnCode] = cellValue;
      }
      valueData[rowIndex] = row;
      const newTableValue = [...valueData];

      onChange(newTableValue);
    }
  };

  let filteredData = valueData;
  let valueDataPage = valueData.slice(currentPage * itemsPerPage, (currentPage + 1) * itemsPerPage);

  const cellMatchSearch = (cellAsString: string) => {
    return isSearching && cellAsString.indexOf(searchText) >= 0;
  };

  if (isSearching) {
    filteredData = valueData.filter(row => {
      return tableConfiguration
        .map(columnDefinition => columnDefinition.code)
        .some(columnCode => {
          return cellMatchSearch(`${row[columnCode]}`);
        });
    });
    valueDataPage = filteredData.slice(0, itemsPerPage);
  }

  const [firstColumn, ...otherColumns] = tableConfiguration;

  const getOptionLabel = async (columnCode: ColumnCode, value: string) => {
    const selectOption = await getSelectOption(router, attributeCode, columnCode, value);

    return selectOption ? getLabel(selectOption.labels, userContext.get('catalogLocale'), selectOption.code) : value;
  };

  React.useEffect(() => {
    const f = async () => {
      for await (const column of tableConfiguration.filter(
        columnDefinition => columnDefinition.data_type === 'select'
      )) {
        options[column.code] = (await getSelectOptions(router, attributeCode, column.code)) || [];
      }
      setOptions({...options});

      for await (const row of valueDataPage) {
        selectOptionLabels[`${firstColumn.code}-${row[firstColumn.code]}`] = await getOptionLabel(
          firstColumn.code,
          row[firstColumn.code] as string
        );
      }
      setSelectOptionLabels({...selectOptionLabels});
    };
    f();
  }, [valueDataPage.length]);

  const isInError = (rowIndex: number, columnCode: ColumnCode) => {
    return violatedCells.some(violatedCell => violatedCell.rowIndex === rowIndex && violatedCell.columnCode === columnCode);
  }

  return (
    <TableInputContainer>
      <TableInput>
        <TableInput.Header>
          {tableConfiguration.map(columnDefinition => (
            <TableInput.HeaderCell key={columnDefinition.code}>
              {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
            </TableInput.HeaderCell>
          ))}
        </TableInput.Header>
        <TableInput.Body>
          {/* TODO ça marchera pas avec la pagination */}
          {valueDataPage.map((row, index) => {
            return (
              <TableInput.Row key={row['unique id']}>
                <TableInput.Cell rowTitle={true} inError={isInError(index, firstColumn.code)}>
                  {selectOptionLabels[`${firstColumn.code}-${row[firstColumn.code]}`] ?? ''}
                </TableInput.Cell>
                {otherColumns.map(columnDefinition => {
                  const columnCode = columnDefinition.code;
                  const columnType = columnDefinition.data_type;

                  return (
                    <TableInput.Cell key={`${row['unique id']}-${columnCode}`}>
                      {'number' === columnType && (
                        <TableInput.Number
                          value={`${row[columnCode] as number}`}
                          onChange={(value: string) => handleChange(row['unique id'], columnCode, value)}
                          highlighted={cellMatchSearch(`${row[columnCode]}`)}
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          inError={isInError(index, columnCode)}
                        />
                      )}
                      {'text' === columnType && (
                        <TableInput.Text
                          value={`${row[columnCode]}`}
                          onChange={(value: string) => handleChange(row['unique id'], columnCode, value)}
                          highlighted={cellMatchSearch(`${row[columnCode]}`)}
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          inError={isInError(index, columnCode)}
                        />
                      )}
                      {'select' === columnType && (
                        <TableInputSelect
                          value={row[columnCode] as SelectOptionCode | undefined}
                          onChange={(value: SelectOptionCode | undefined) =>
                            handleChange(row['unique id'], columnCode, value)
                          }
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          options={options[columnCode]}
                          inError={isInError(index, columnCode)}
                        />
                      )}
                      {'boolean' === columnType && (
                        <TableInput.Boolean
                          value={typeof row[columnCode] === 'undefined' ? null : (row[columnCode] as boolean | null)}
                          onChange={(value: boolean | null) =>
                            handleChange(row['unique id'], columnCode, null === value ? undefined : value)
                          }
                          yesLabel={translate('pim_common.yes')}
                          noLabel={translate('pim_common.no')}
                          clearLabel={translate('pim_common.clear')}
                          openDropdownLabel={translate('pim_common.open')}
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          inError={isInError(index, columnCode)}
                        />
                      )}
                    </TableInput.Cell>
                  );
                })}
              </TableInput.Row>
            );
          })}
        </TableInput.Body>
      </TableInput>
      {isSearching && valueDataPage.length === 0 && (
        <CenteredHelper>
          <AddingValueIllustration size={120} />
          {translate('pim_table_attribute.form.product.no_search_result')}
        </CenteredHelper>
      )}
      <TableFooter
        itemsPerPage={itemsPerPage}
        currentPage={currentPage}
        rowsCount={filteredData.length}
        setCurrentPage={setCurrentPage}
        setItemsPerPage={setItemsPerPage}
      />
    </TableInputContainer>
  );
};

export {TableInputValue, TABLE_VALUE_ITEMS_PER_PAGE};
