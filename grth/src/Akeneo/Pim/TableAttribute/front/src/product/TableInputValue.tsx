import React from 'react';
import {
  AddingValueIllustration,
  AkeneoThemedProps,
  Dropdown,
  getColor,
  IconButton,
  MoreVerticalIcon,
  Placeholder,
  TableInput,
} from 'akeneo-design-system';
import {ColumnCode, ColumnDefinition, RecordCode, SelectOptionCode, TableCell} from '../models';
import {TableFooter} from './TableFooter';
import styled from 'styled-components';
import {TableRowWithId, TableValueWithId, ViolatedCell} from './TableFieldApp';
import {getLabel, useTranslate} from '@akeneo-pim-community/shared';
import {UNIQUE_ID_KEY} from './useUniqueIds';
import {useAttributeContext, useLocaleCode} from '../contexts';
import {RecordCellIndex, SelectCellIndex} from './CellIndexes';
import {usePrefetchTableValueRecords} from './usePrefetchTableValueRecords';
import {cellMatchers} from './CellMatchers';
import {cellInputs} from './CellInputs';

const TABLE_VALUE_ITEMS_PER_PAGE = [10, 20, 50, 100];

const BorderedPlaceholder = styled(Placeholder)`
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
    line-height: 0;
  }
`;

const HeaderActionsCell = styled(TableInput.HeaderCell)`
  max-width: 34px;
  min-width: 34px;
  width: 34px;
`;

/**
 * CAN_EDIT: the user has permission to edit
 * CANNOT_EDIT: the user has not permission to edit
 * READ_ONLY: the input is in read only, no matter the permission
 */
type Visibility = 'CAN_EDIT' | 'CANNOT_EDIT' | 'READ_ONLY';

type TableInputValueProps = {
  valueData: TableValueWithId;
  visibility: Visibility;
  onChange?: (tableValue: TableValueWithId) => void;
  searchText?: string;
  violatedCells?: ViolatedCell[];
  isCopying?: boolean;
};

const TableInputValue: React.FC<TableInputValueProps> = ({
  valueData,
  onChange,
  visibility,
  searchText = '',
  violatedCells = [],
  isCopying = false,
}) => {
  const readOnly = ['CANNOT_EDIT', 'READ_ONLY'].includes(visibility);
  const translate = useTranslate();
  const {attribute, setAttribute} = useAttributeContext();
  const [itemsPerPage, setItemsPerPage] = React.useState<number>(TABLE_VALUE_ITEMS_PER_PAGE[0]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const [dirtyCells, setDirtyCells] = React.useState<ViolatedCell[]>([]);
  const [isActionsOpened, setActionsOpened] = React.useState<string | undefined>();
  const isSearching = searchText !== '';
  const isDragAndDroppable = !readOnly && !isSearching;
  const areRecordsPrefetched = usePrefetchTableValueRecords(valueData);
  const localeCode = useLocaleCode();

  const matchers: {[data_type: string]: (cell: TableCell, searchText: string, columnCode: ColumnCode) => boolean} = {};
  Object.keys(cellMatchers).forEach(data_type => {
    matchers[data_type] = cellMatchers[data_type]();
  });

  React.useEffect(() => {
    setCurrentPage(0);
  }, [searchText]);

  const addDirtyCell = (id: string, columnCode: ColumnCode | undefined) => {
    if (attribute) {
      if (typeof columnCode === 'undefined') {
        attribute.table_configuration.forEach(columnDefinition =>
          dirtyCells.push({id, columnCode: columnDefinition.code})
        );
      } else {
        dirtyCells.push({id, columnCode});
      }
      setDirtyCells([...dirtyCells]);
    }
  };

  const handleChange = (uniqueId: string, columnCode: ColumnCode, cellValue: TableCell | undefined) => {
    const rowIndex = valueData.findIndex(row => row[UNIQUE_ID_KEY] === uniqueId);
    addDirtyCell(uniqueId, columnCode);
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
    const matcher = matchers[columnDefinition.data_type];

    return matcher && matcher(cell, searchText, columnDefinition.code);
  };

  if (isSearching && attribute) {
    filteredData = valueData.filter(row => {
      return attribute.table_configuration.some(columnDefinition => {
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
    return (
      violatedCells.some(violatedCell => violatedCell.id === id && violatedCell.columnCode === columnCode) &&
      !dirtyCells.some(dirtyCell => dirtyCell.id === id && dirtyCell.columnCode === columnCode)
    );
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
    addDirtyCell(uniqueId, undefined);
    onChange?.(valueData.filter(row => row[UNIQUE_ID_KEY] !== uniqueId));
  };

  const handleClearRow = (uniqueId: string) => {
    closeActions();
    addDirtyCell(uniqueId, undefined);
    const rowIndex = valueData.findIndex(row => row[UNIQUE_ID_KEY] === uniqueId);
    if (rowIndex >= 0) {
      const row = valueData[rowIndex];
      const newRow: TableRowWithId = {[UNIQUE_ID_KEY]: row[UNIQUE_ID_KEY]};
      newRow[firstColumn.code] = row[firstColumn.code];

      valueData[rowIndex] = newRow;
      const newTableValue = [...valueData];

      onChange?.(newTableValue);
    }
  };

  const handleMoveFirst = (uniqueId: string) => {
    closeActions();
    const rowIndex = valueData.findIndex(row => row[UNIQUE_ID_KEY] === uniqueId);
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
    const rowIndex = valueData.findIndex(row => row[UNIQUE_ID_KEY] === uniqueId);
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

  const tableInputCell = (row: TableRowWithId, columnDefinition: ColumnDefinition) => {
    const CellInput = cellInputs[columnDefinition.data_type];
    const matchSearch = matchers[columnDefinition.data_type];
    const columnCode = columnDefinition.code;
    const cell = row[columnCode];

    return (
      attribute && (
        <CellInput
          row={row}
          columnDefinition={columnDefinition}
          onChange={value => handleChange(row[UNIQUE_ID_KEY], columnCode, value)}
          data-testid={`input-${row[UNIQUE_ID_KEY]}-${columnCode}`}
          inError={isInErrorFromBackend(row[UNIQUE_ID_KEY], columnCode)}
          highlighted={matchSearch(cell, searchText, columnCode)}
          attribute={attribute}
          setAttribute={setAttribute}
        />
      )
    );
  };

  const tableIndexCell = (row: TableRowWithId) => {
    return firstColumn.data_type === 'select' ? (
      <SelectCellIndex
        isInErrorFromBackend={isInErrorFromBackend(row[UNIQUE_ID_KEY], firstColumn.code)}
        searchText={searchText}
        value={row[firstColumn.code] as SelectOptionCode}
      />
    ) : (
      <RecordCellIndex searchText={searchText} value={row[firstColumn.code] as RecordCode} />
    );
  };

  const [firstColumn, ...otherColumns] = attribute?.table_configuration || [];

  return (
    <>
      {attribute && areRecordsPrefetched && (
        <TableInputContainer isCopying={isCopying}>
          <TableInput
            readOnly={readOnly}
            isDragAndDroppable={isDragAndDroppable}
            onReorder={isDragAndDroppable ? handleReorder : undefined}
          >
            <TableInput.Header>
              {attribute.table_configuration.map(columnDefinition => (
                <TableInput.HeaderCell key={columnDefinition.code}>
                  {getLabel(columnDefinition.labels, localeCode, columnDefinition.code)}
                </TableInput.HeaderCell>
              ))}
              <HeaderActionsCell />
            </TableInput.Header>
            <TableInputValueBody>
              {valueDataPage.map(row => {
                return (
                  <TableInput.Row key={row[UNIQUE_ID_KEY]} highlighted={isOpenActions(row[UNIQUE_ID_KEY])}>
                    <TableInput.Cell>{tableIndexCell(row)}</TableInput.Cell>
                    {otherColumns.map(columnDefinition => {
                      return (
                        <TableInput.Cell key={`${row[UNIQUE_ID_KEY]}-${columnDefinition.code}`}>
                          {tableInputCell(row, columnDefinition)}
                        </TableInput.Cell>
                      );
                    })}
                    <TableInput.Cell>
                      {!readOnly && (
                        <Dropdown>
                          <IconButton
                            icon={<MoreVerticalIcon size={16} />}
                            title={translate('pim_common.actions')}
                            onClick={() => openActions(row[UNIQUE_ID_KEY])}
                            ghost='borderless'
                            level='tertiary'
                          />
                          {isOpenActions(row[UNIQUE_ID_KEY]) && (
                            <Dropdown.Overlay verticalPosition='down' onClose={closeActions}>
                              <Dropdown.ItemCollection>
                                <Dropdown.Item onClick={() => handleDeleteRow(row[UNIQUE_ID_KEY])}>
                                  {translate('pim_table_attribute.form.product.actions.delete_row')}
                                </Dropdown.Item>
                                <Dropdown.Item onClick={() => handleClearRow(row[UNIQUE_ID_KEY])}>
                                  {translate('pim_table_attribute.form.product.actions.clear_row')}
                                </Dropdown.Item>
                                <Dropdown.Item onClick={() => handleMoveFirst(row[UNIQUE_ID_KEY])}>
                                  {translate('pim_table_attribute.form.product.actions.move_first')}
                                </Dropdown.Item>
                                <Dropdown.Item onClick={() => handleMoveLast(row[UNIQUE_ID_KEY])}>
                                  {translate('pim_table_attribute.form.product.actions.move_last')}
                                </Dropdown.Item>
                              </Dropdown.ItemCollection>
                            </Dropdown.Overlay>
                          )}
                        </Dropdown>
                      )}
                    </TableInput.Cell>
                  </TableInput.Row>
                );
              })}
            </TableInputValueBody>
          </TableInput>
          {isSearching && valueDataPage.length === 0 && (
            <BorderedPlaceholder
              illustration={<AddingValueIllustration />}
              title={translate('pim_table_attribute.form.product.no_search_result')}
            />
          )}
          {!isSearching && valueDataPage.length === 0 && (
            <BorderedPlaceholder
              illustration={<AddingValueIllustration />}
              title={translate('pim_table_attribute.form.product.no_rows_title', {
                attributeLabel: getLabel(attribute.labels, localeCode, attribute.code),
              })}
            >
              {visibility === 'CANNOT_EDIT' &&
                translate('pim_table_attribute.form.product.no_rows_subtitle_no_permission')}
              {visibility === 'CAN_EDIT' && translate('pim_table_attribute.form.product.no_rows_subtitle')}
            </BorderedPlaceholder>
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
      )}
    </>
  );
};

export {TableInputValue, TABLE_VALUE_ITEMS_PER_PAGE};
