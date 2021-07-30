import React from 'react';
import {AddingValueIllustration, AkeneoThemedProps, getColor, getFontSize, TableInput} from 'akeneo-design-system';
import {
  ColumnCode,
  ColumnDefinition,
  NumberColumnValidation,
  SelectOption,
  SelectOptionCode,
  TableConfiguration,
  TextColumnValidation,
} from '../models/TableConfiguration';
import {
  getLabel,
  LoadingPlaceholderContainer,
  useRouter,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {TableFooter} from './TableFooter';
import styled from 'styled-components';
import {TableValueWithId, ViolatedCell} from './TableFieldApp';
import {getSelectOption, getSelectOptions} from '../repositories/SelectOption';
import {TableInputSelect} from './CellInputs/TableInputSelect';
import {TableCell} from '../models/TableValue';
import {TableInputNumber} from './CellInputs/TableInputNumber';
import {TableInputText} from './CellInputs/TableInputText';
import {TableAttribute} from '../models/Attribute';

const TABLE_VALUE_ITEMS_PER_PAGE = [10, 20, 50, 100];

const TableInputContainer = styled.div<{isCopying: boolean} & AkeneoThemedProps>`
  width: ${({isCopying}) => (isCopying ? '460px' : '100%')};
`;

const CenteredHelper = styled.div`
  border: 1px solid ${getColor('grey', 80)};
  padding-bottom: 20px;
  text-align: center;
  color: ${getColor('grey', 100)};
  & > * {
    display: block;
    margin: auto;
  }
`;

const CenteredHelperTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

type TableInputValueProps = {
  attribute: TableAttribute;
  valueData: TableValueWithId;
  tableConfiguration: TableConfiguration;
  onChange?: (tableValue: TableValueWithId) => void;
  searchText?: string;
  violatedCells?: ViolatedCell[];
  readOnly?: boolean;
  isCopying?: boolean;
};

const TableInputValue: React.FC<TableInputValueProps> = ({
  attribute,
  valueData,
  tableConfiguration,
  onChange,
  readOnly = false,
  searchText = '',
  violatedCells = [],
  isCopying = false,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useRouter();
  const [itemsPerPage, setItemsPerPage] = React.useState<number>(TABLE_VALUE_ITEMS_PER_PAGE[0]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const [selectOptionLabels, setSelectOptionLabels] = React.useState<{[key: string]: string | null}>({});
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

      onChange?.(newTableValue);
    }
  };

  let filteredData = valueData;
  let valueDataPage = valueData.slice(currentPage * itemsPerPage, (currentPage + 1) * itemsPerPage);

  const cellMatchSearch = (cell: TableCell, columnDefinition: ColumnDefinition) => {
    if (!isSearching || typeof cell === 'undefined') {
      return false;
    }

    switch (columnDefinition.data_type) {
      case 'text':
        return (cell as string).toLowerCase().includes(searchText.toLowerCase());
      case 'number':
        return `${cell}`.toLowerCase().includes(searchText.toLowerCase());
      case 'boolean':
        return translate(cell ? 'pim_common.yes' : 'pim_common.no')
          .toLowerCase()
          .includes(searchText.toLowerCase());
      case 'select': {
        const option = (options[columnDefinition.code] || []).find(option => option.code === cell);
        if (!option) {
          return false;
        }
        return getLabel(option.labels, userContext.get('catalogLocale'), option.code)
          .toLowerCase()
          .includes(searchText.toLowerCase());
      }
    }
  };

  if (isSearching) {
    filteredData = valueData.filter(row => {
      return tableConfiguration.some(columnDefinition => {
        return cellMatchSearch(row[columnDefinition.code], columnDefinition);
      });
    });
    valueDataPage = filteredData.slice(0, itemsPerPage);
  }

  const [firstColumn, ...otherColumns] = tableConfiguration;

  const getOptionLabel = async (columnCode: ColumnCode, value: string) => {
    const selectOption = await getSelectOption(router, attribute.code, columnCode, value);

    return selectOption ? getLabel(selectOption.labels, userContext.get('catalogLocale'), selectOption.code) : null;
  };

  React.useEffect(() => {
    const f = async () => {
      for await (const column of tableConfiguration.filter(
        columnDefinition => columnDefinition.data_type === 'select'
      )) {
        options[column.code] = (await getSelectOptions(router, attribute.code, column.code)) || [];
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

  const isInErrorFromBackend = (id: string, columnCode: ColumnCode) => {
    return violatedCells.some(violatedCell => violatedCell.id === id && violatedCell.columnCode === columnCode);
  };

  return (
    <TableInputContainer isCopying={isCopying}>
      <TableInput readOnly={readOnly}>
        <TableInput.Header>
          {tableConfiguration.map(columnDefinition => (
            <TableInput.HeaderCell key={columnDefinition.code}>
              {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
            </TableInput.HeaderCell>
          ))}
        </TableInput.Header>
        <TableInput.Body>
          {valueDataPage.map(row => {
            return (
              <TableInput.Row key={row['unique id']}>
                <TableInput.Cell
                  rowTitle={true}
                  highlighted={cellMatchSearch(row[firstColumn.code], firstColumn)}
                  inError={
                    isInErrorFromBackend(row['unique id'], firstColumn.code) ||
                    selectOptionLabels[`${firstColumn.code}-${row[firstColumn.code]}`] === null
                  }>
                  {typeof selectOptionLabels[`${firstColumn.code}-${row[firstColumn.code]}`] === 'undefined' ? (
                    <LoadingPlaceholderContainer>
                      <div>{translate('pim_common.loading')}</div>
                    </LoadingPlaceholderContainer>
                  ) : (
                    selectOptionLabels[`${firstColumn.code}-${row[firstColumn.code]}`] || `[${row[firstColumn.code]}]`
                  )}
                </TableInput.Cell>
                {otherColumns.map(columnDefinition => {
                  const columnCode = columnDefinition.code;
                  const columnType = columnDefinition.data_type;

                  return (
                    <TableInput.Cell key={`${row['unique id']}-${columnCode}`}>
                      {'number' === columnType && (
                        <TableInputNumber
                          highlighted={cellMatchSearch(row[columnCode], columnDefinition)}
                          value={row[columnCode] as string | undefined}
                          onChange={value => handleChange(row['unique id'], columnCode, value)}
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          validations={columnDefinition.validations as NumberColumnValidation}
                          inError={isInErrorFromBackend(row['unique id'], columnCode)}
                        />
                      )}
                      {'text' === columnType && (
                        <TableInputText
                          highlighted={cellMatchSearch(row[columnCode], columnDefinition)}
                          value={row[columnCode] as string | undefined}
                          onChange={(value: string) => handleChange(row['unique id'], columnCode, value)}
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          validations={columnDefinition.validations as TextColumnValidation}
                          inError={isInErrorFromBackend(row['unique id'], columnCode)}
                        />
                      )}
                      {'select' === columnType && (
                        <TableInputSelect
                          attribute={attribute}
                          highlighted={cellMatchSearch(row[columnCode], columnDefinition)}
                          value={row[columnCode] as SelectOptionCode | undefined}
                          onChange={(value: SelectOptionCode | undefined) =>
                            handleChange(row['unique id'], columnCode, value)
                          }
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          options={options[columnCode]}
                          inError={isInErrorFromBackend(row['unique id'], columnCode)}
                        />
                      )}
                      {'boolean' === columnType && (
                        <TableInput.Boolean
                          highlighted={cellMatchSearch(row[columnCode], columnDefinition)}
                          value={typeof row[columnCode] === 'undefined' ? null : (row[columnCode] as boolean | null)}
                          onChange={(value: boolean | null) =>
                            handleChange(row['unique id'], columnCode, null === value ? undefined : value)
                          }
                          yesLabel={translate('pim_common.yes')}
                          noLabel={translate('pim_common.no')}
                          clearLabel={translate('pim_common.clear')}
                          openDropdownLabel={translate('pim_common.open')}
                          data-testid={`input-${row['unique id']}-${columnCode}`}
                          inError={isInErrorFromBackend(row['unique id'], columnCode)}
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
      {!isSearching && valueDataPage.length === 0 && (
        <CenteredHelper>
          <AddingValueIllustration size={120} />
          <CenteredHelperTitle>
            {translate('pim_table_attribute.form.product.no_rows_title', {
              attributeLabel: getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code),
            })}
          </CenteredHelperTitle>
          {translate('pim_table_attribute.form.product.no_rows_subtitle')}
        </CenteredHelper>
      )}
      {valueData.length > TABLE_VALUE_ITEMS_PER_PAGE[0] && (
        <TableFooter
          itemsPerPage={itemsPerPage}
          currentPage={currentPage}
          rowsCount={filteredData.length}
          setCurrentPage={setCurrentPage}
          setItemsPerPage={setItemsPerPage}
        />
      )}
    </TableInputContainer>
  );
};

export {TableInputValue, TABLE_VALUE_ITEMS_PER_PAGE};
