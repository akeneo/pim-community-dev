import React from 'react';
import {
  AddingValueIllustration,
  AkeneoThemedProps,
  Dropdown,
  getColor,
  IconButton,
  MoreVerticalIcon,
  TableInput,
} from 'akeneo-design-system';
import {
  ColumnCode,
  ColumnDefinition,
  NumberColumnValidation,
  SelectOptionCode,
  TableConfiguration,
  TextColumnValidation,
} from '../models/TableConfiguration';
import {getLabel, LoadingPlaceholderContainer, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {TableFooter} from './TableFooter';
import styled from 'styled-components';
import {TableRowWithId, TableValueWithId, ViolatedCell} from './TableFieldApp';
import {TableInputSelect} from './CellInputs/TableInputSelect';
import {TableCell} from '../models/TableValue';
import {TableInputNumber} from './CellInputs/TableInputNumber';
import {TableInputText} from './CellInputs/TableInputText';
import {TableAttribute} from '../models/Attribute';
import {CenteredHelper} from '../shared/CenteredHelper';
import {useFetchOptions} from './useFetchOptions';

const TABLE_VALUE_ITEMS_PER_PAGE = [10, 20, 50, 100];

const BorderedCenteredHelper = styled(CenteredHelper)`
  border: 1px solid ${getColor('grey', 80)};
  padding-bottom: 20px;
`;

const TableInputContainer = styled.div<{isCopying: boolean} & AkeneoThemedProps>`
  width: ${({isCopying}) => (isCopying ? '460px' : '100%')};
`;

const TableInputValueBody = styled(TableInput.Body)`
  & > tr > td:last-child {
    max-width: 34px;
    min-width: 34px;
    width: 34px;
    border-left: none;
  }
`;

const HeaderActionsCell = styled(TableInput.HeaderCell)`
  max-width: 34px;
  min-width: 34px;
  width: 34px;
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
  const [itemsPerPage, setItemsPerPage] = React.useState<number>(TABLE_VALUE_ITEMS_PER_PAGE[0]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const [currentViolatedCells, setCurrentViolatedCells] = React.useState<ViolatedCell[]>(violatedCells);
  const [isActionsOpened, setActionsOpened] = React.useState<string | undefined>();
  const isSearching = searchText !== '';
  const isDragAndDroppable = !readOnly && !isSearching;
  const [firstColumn, ...otherColumns] = tableConfiguration;
  const {getOptionsFromColumnCode, getOptionLabel} = useFetchOptions(tableConfiguration, attribute, valueData);

  React.useEffect(() => {
    setCurrentPage(0);
  }, [searchText]);

  const deleteValidationErrors = (uniqueId: string, columnCode: ColumnCode | undefined) => {
    const newViolatedCells = currentViolatedCells.filter((cell: ViolatedCell) => {
      return !(cell.id === uniqueId && (typeof columnCode === 'undefined' || cell.columnCode === columnCode));
    });
    setCurrentViolatedCells(newViolatedCells);
  };

  const handleChange = (uniqueId: string, columnCode: ColumnCode, cellValue: TableCell | undefined) => {
    const rowIndex = valueData.findIndex(row => row['unique id'] === uniqueId);
    deleteValidationErrors(uniqueId, columnCode);
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
        const option = (getOptionsFromColumnCode(columnDefinition.code) || []).find(option => option.code === cell);
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

  React.useEffect(() => {
    const currentPageReal = currentPage + 1;
    const pageCount = Math.ceil(filteredData.length / itemsPerPage);
    if (currentPageReal > pageCount) {
      setCurrentPage(Math.max(0, pageCount - 1));
    }
  }, [valueData.length, filteredData.length, itemsPerPage, currentPage, setCurrentPage]);

  const isInErrorFromBackend = (id: string, columnCode: ColumnCode) => {
    return currentViolatedCells.some(violatedCell => violatedCell.id === id && violatedCell.columnCode === columnCode);
  };

  const handleReorder = (indexesFromPage: number[]) => {
    const newIndexes = [];
    for (let i = 0; i < valueData.length; i++) {
      newIndexes.push(
        i < itemsPerPage * currentPage || i >= itemsPerPage * (currentPage + 1)
          ? i
          : indexesFromPage[i - itemsPerPage * currentPage] + itemsPerPage * currentPage
      );
    }
    const newTableValue = newIndexes.map(i => valueData[i]);
    onChange?.(newTableValue);
  };

  const openActions = (uniqueId: string) => setActionsOpened(uniqueId);
  const closeActions = () => setActionsOpened(undefined);
  const isOpenActions = (uniqueId: string) => isActionsOpened === uniqueId;

  const handleDeleteRow = (uniqueId: string) => {
    deleteValidationErrors(uniqueId, undefined);
    onChange?.(valueData.filter(row => row['unique id'] !== uniqueId));
  };

  const handleClearRow = (uniqueId: string) => {
    closeActions();
    deleteValidationErrors(uniqueId, undefined);
    const rowIndex = valueData.findIndex(row => row['unique id'] === uniqueId);
    if (rowIndex >= 0) {
      const row = valueData[rowIndex];
      const newRow: TableRowWithId = {'unique id': row['unique id']};
      newRow[firstColumn.code] = row[firstColumn.code];

      valueData[rowIndex] = newRow;
      const newTableValue = [...valueData];

      onChange?.(newTableValue);
    }
  };

  const handleMoveFirst = (uniqueId: string) => {
    closeActions();
    const rowIndex = valueData.findIndex(row => row['unique id'] === uniqueId);
    if (rowIndex >= 0) {
      const indexes = [rowIndex];
      for (let i = 0; i < valueData.length; i++) {
        if (i !== rowIndex) indexes.push(i);
      }
      const newTableValue = indexes.map(index => valueData[index]);
      onChange?.(newTableValue);
    }
  };

  const handleMoveLast = (uniqueId: string) => {
    closeActions();
    const rowIndex = valueData.findIndex(row => row['unique id'] === uniqueId);
    if (rowIndex >= 0) {
      const indexes = [];
      for (let i = 0; i < valueData.length; i++) {
        if (i !== rowIndex) indexes.push(i);
      }
      indexes.push(rowIndex);

      const newTableValue = indexes.map(index => valueData[index]);
      onChange?.(newTableValue);
    }
  };

  return (
    <TableInputContainer isCopying={isCopying}>
      <TableInput
        readOnly={readOnly}
        isDragAndDroppable={isDragAndDroppable}
        onReorder={isDragAndDroppable ? handleReorder : undefined}>
        <TableInput.Header>
          {tableConfiguration.map(columnDefinition => (
            <TableInput.HeaderCell key={columnDefinition.code}>
              {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
            </TableInput.HeaderCell>
          ))}
          <HeaderActionsCell />
        </TableInput.Header>
        <TableInputValueBody>
          {valueDataPage.map(row => {
            return (
              <TableInput.Row key={row['unique id']}>
                <TableInput.Cell
                  rowTitle={true}
                  highlighted={cellMatchSearch(row[firstColumn.code], firstColumn)}
                  inError={
                    isInErrorFromBackend(row['unique id'], firstColumn.code) ||
                    getOptionLabel(firstColumn.code, row[firstColumn.code]) === null
                  }>
                  {typeof getOptionLabel(firstColumn.code, row[firstColumn.code]) === 'undefined' ? (
                    <LoadingPlaceholderContainer>
                      <div>{translate('pim_common.loading')}</div>
                    </LoadingPlaceholderContainer>
                  ) : (
                    getOptionLabel(firstColumn.code, row[firstColumn.code]) || `[${row[firstColumn.code]}]`
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
                          options={getOptionsFromColumnCode(columnCode)}
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
                <TableInput.Cell>
                  <Dropdown>
                    <IconButton
                      icon={<MoreVerticalIcon size={16} />}
                      title={translate('pim_common.actions')}
                      onClick={() => openActions(row['unique id'])}
                      ghost='borderless'
                      level='tertiary'
                    />
                    {isOpenActions(row['unique id']) && (
                      <Dropdown.Overlay verticalPosition='down' onClose={closeActions}>
                        <Dropdown.ItemCollection>
                          <Dropdown.Item onClick={() => handleDeleteRow(row['unique id'])}>
                            {translate('pim_table_attribute.form.product.actions.delete_row')}
                          </Dropdown.Item>
                          <Dropdown.Item onClick={() => handleClearRow(row['unique id'])}>
                            {translate('pim_table_attribute.form.product.actions.clear_row')}
                          </Dropdown.Item>
                          <Dropdown.Item onClick={() => handleMoveFirst(row['unique id'])}>
                            {translate('pim_table_attribute.form.product.actions.move_first')}
                          </Dropdown.Item>
                          <Dropdown.Item onClick={() => handleMoveLast(row['unique id'])}>
                            {translate('pim_table_attribute.form.product.actions.move_last')}
                          </Dropdown.Item>
                        </Dropdown.ItemCollection>
                      </Dropdown.Overlay>
                    )}
                  </Dropdown>
                </TableInput.Cell>
              </TableInput.Row>
            );
          })}
        </TableInputValueBody>
      </TableInput>
      {isSearching && valueDataPage.length === 0 && (
        <BorderedCenteredHelper illustration={<AddingValueIllustration />}>
          {translate('pim_table_attribute.form.product.no_search_result')}
        </BorderedCenteredHelper>
      )}
      {!isSearching && valueDataPage.length === 0 && (
        <BorderedCenteredHelper illustration={<AddingValueIllustration />}>
          <CenteredHelper.Title>
            {translate('pim_table_attribute.form.product.no_rows_title', {
              attributeLabel: getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code),
            })}
          </CenteredHelper.Title>
          {translate('pim_table_attribute.form.product.no_rows_subtitle')}
        </BorderedCenteredHelper>
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
