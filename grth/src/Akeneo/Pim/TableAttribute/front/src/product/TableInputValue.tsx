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
import {ColumnCode, ColumnDefinition, TableAttribute, TableCell} from '../models';
import {TableFooter} from './TableFooter';
import styled from 'styled-components';
import {TableRowWithId, TableValueWithId, ViolatedCell} from './TableFieldApp';
import {CenteredHelper, LoadingPlaceholderContainer} from '../shared';
import {useFetchOptions} from './useFetchOptions';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {CellInputsMapping} from './CellInputs';
import {CellMatchersMapping} from './CellMatchers';

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
    line-height: 0;
  }
`;

const HeaderActionsCell = styled(TableInput.HeaderCell)`
  max-width: 34px;
  min-width: 34px;
  width: 34px;
`;

const FirstCellLoadingPlaceholderContainer = styled(LoadingPlaceholderContainer)`
  & > * {
    height: 20px;
  }
`;

type TableInputValueProps = {
  attribute: TableAttribute;
  valueData: TableValueWithId;
  onChange?: (tableValue: TableValueWithId) => void;
  searchText?: string;
  violatedCells?: ViolatedCell[];
  readOnly?: boolean;
  isCopying?: boolean;
  cellInputsMapping: CellInputsMapping;
  cellMatchersMapping: CellMatchersMapping;
};

const TableInputValue: React.FC<TableInputValueProps> = ({
  attribute,
  valueData,
  onChange,
  readOnly = false,
  searchText = '',
  violatedCells = [],
  isCopying = false,
  cellInputsMapping,
  cellMatchersMapping,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const [itemsPerPage, setItemsPerPage] = React.useState<number>(TABLE_VALUE_ITEMS_PER_PAGE[0]);
  const [currentPage, setCurrentPage] = React.useState<number>(0);
  const [dirtyCells, setDirtyCells] = React.useState<ViolatedCell[]>([]);
  const [isActionsOpened, setActionsOpened] = React.useState<string | undefined>();
  const isSearching = searchText !== '';
  const isDragAndDroppable = !readOnly && !isSearching;
  const [firstColumn, ...otherColumns] = attribute.table_configuration;
  const {getOptionLabel} = useFetchOptions(attribute.table_configuration, attribute.code, valueData);

  const matchers: {[data_type: string]: (cell: TableCell, searchText: string, columnCode: ColumnCode) => boolean} = {};
  Object.keys(cellInputsMapping).forEach(data_type => {
    matchers[data_type] = cellMatchersMapping[data_type].default(attribute, valueData);
  });

  React.useEffect(() => {
    setCurrentPage(0);
  }, [searchText]);

  const addDirtyCell = (id: string, columnCode: ColumnCode | undefined) => {
    if (typeof columnCode === 'undefined') {
      attribute.table_configuration.forEach(columnDefinition =>
        dirtyCells.push({id, columnCode: columnDefinition.code})
      );
    } else {
      dirtyCells.push({id, columnCode});
    }
    setDirtyCells([...dirtyCells]);
  };

  const handleChange = (uniqueId: string, columnCode: ColumnCode, cellValue: TableCell | undefined) => {
    const rowIndex = valueData.findIndex(row => row['unique id'] === uniqueId);
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

  if (isSearching) {
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
    onChange?.(valueData.filter(row => row['unique id'] !== uniqueId));
  };

  const handleClearRow = (uniqueId: string) => {
    closeActions();
    addDirtyCell(uniqueId, undefined);
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

  const tableInputCell = (row: TableRowWithId, columnDefinition: ColumnDefinition) => {
    const CellInput = cellInputsMapping[columnDefinition.data_type]?.default;
    if (CellInput) {
      const matchSearch = matchers[columnDefinition.data_type];
      const columnCode = columnDefinition.code;
      const cell = row[columnCode];

      return (
        <CellInput
          row={row}
          columnDefinition={columnDefinition}
          onChange={value => handleChange(row['unique id'], columnCode, value)}
          data-testid={`input-${row['unique id']}-${columnCode}`}
          inError={isInErrorFromBackend(row['unique id'], columnCode)}
          attribute={attribute}
          highlighted={matchSearch(cell, searchText, columnCode)}
        />
      );
    }

    return null;
  };

  return (
    <TableInputContainer isCopying={isCopying}>
      <TableInput
        readOnly={readOnly}
        isDragAndDroppable={isDragAndDroppable}
        onReorder={isDragAndDroppable ? handleReorder : undefined}>
        <TableInput.Header>
          {attribute.table_configuration.map(columnDefinition => (
            <TableInput.HeaderCell key={columnDefinition.code}>
              {getLabel(columnDefinition.labels, userContext.get('catalogLocale'), columnDefinition.code)}
            </TableInput.HeaderCell>
          ))}
          <HeaderActionsCell />
        </TableInput.Header>
        <TableInputValueBody>
          {valueDataPage.map(row => {
            return (
              <TableInput.Row key={row['unique id']} highlighted={isOpenActions(row['unique id'])}>
                <TableInput.Cell>
                  <TableInput.CellContent
                    rowTitle={true}
                    highlighted={cellMatchSearch(row[firstColumn.code], firstColumn)}
                    inError={
                      isInErrorFromBackend(row['unique id'], firstColumn.code) ||
                      getOptionLabel(firstColumn.code, row[firstColumn.code]) === null
                    }>
                    {typeof getOptionLabel(firstColumn.code, row[firstColumn.code]) === 'undefined' ? (
                      <FirstCellLoadingPlaceholderContainer>
                        <div>{translate('pim_common.loading')}</div>
                      </FirstCellLoadingPlaceholderContainer>
                    ) : (
                      getOptionLabel(firstColumn.code, row[firstColumn.code]) || `[${row[firstColumn.code]}]`
                    )}
                  </TableInput.CellContent>
                </TableInput.Cell>
                {otherColumns.map(columnDefinition => {
                  return (
                    <TableInput.Cell key={`${row['unique id']}-${columnDefinition.code}`}>
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
                  )}
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
          {readOnly
            ? translate('pim_table_attribute.form.product.no_rows_subtitle_on_readonly')
            : translate('pim_table_attribute.form.product.no_rows_subtitle')}
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
