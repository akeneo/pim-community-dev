import React, {useCallback, useEffect, useState} from 'react';
import styled from 'styled-components';
import {uuid} from 'akeneo-design-system';
import {ValidationError} from '@akeneo-pim-community/shared';
import {ColumnDetails, ColumnList, ColumnDetailsPlaceholder} from './components';
import {ValidationErrorsContext} from './contexts/ValidationErrorsContext';
import {
  addColumn,
  createColumn,
  removeColumn,
  ColumnConfiguration,
  updateColumn,
  MAX_COLUMN_COUNT,
  ColumnsState,
} from './models/ColumnConfiguration';

const Container = styled.div`
  padding-top: 10px;
  display: flex;
  gap: 20px;
  height: calc(100vh - 278px);
`;

type ColumnsTabProps = {
  columnsConfiguration: ColumnConfiguration[];
  validationErrors: ValidationError[];
  onColumnsConfigurationChange: (columnsConfiguration: ColumnConfiguration[]) => void;
};

const ColumnsTab = ({
  columnsConfiguration: initial,
  validationErrors,
  onColumnsConfigurationChange,
}: ColumnsTabProps) => {
  const [columnsState, setColumnsState] = useState<ColumnsState>({
    columns: initial,
    selectedColumnUuid: initial[0]?.uuid ?? null,
  });

  useEffect(() => {
    onColumnsConfigurationChange(columnsState.columns);
  }, [onColumnsConfigurationChange, columnsState.columns]);

  const handleCreateColumn = useCallback((newColumnName: string) => {
    const column = createColumn(newColumnName, uuid());
    setColumnsState(previousColumnsState => ({
      columns: addColumn(previousColumnsState.columns, column),
      selectedColumnUuid: column.uuid,
    }));
  }, []);

  const handleCreateColumns = useCallback((newColumnNames: string[]) => {
    setColumnsState(previousColumnsState => {
      const columns = newColumnNames.reduce((existingColumns, newColumnName) => {
        if (existingColumns.length === MAX_COLUMN_COUNT) return existingColumns;

        return addColumn(existingColumns, createColumn(newColumnName, uuid()));
      }, previousColumnsState.columns);

      return {columns, selectedColumnUuid: columns[columns.length - 1].uuid};
    });
  }, []);

  const handleRemoveColumn = useCallback((columnUuid: string) => {
    setColumnsState(previousColumnsState => ({
      ...previousColumnsState,
      columns: removeColumn(previousColumnsState.columns, columnUuid),
    }));
  }, []);

  const handleSelectColumn = useCallback((selectedColumnUuid: string | null) => {
    setColumnsState(previousColumnsState => ({
      ...previousColumnsState,
      selectedColumnUuid,
    }));
  }, []);

  const handleChangeColumn = useCallback((column: ColumnConfiguration) => {
    setColumnsState(previousColumnsState => ({
      ...previousColumnsState,
      columns: updateColumn(previousColumnsState.columns, column),
    }));
  }, []);

  const handleReorderColumns = useCallback((newIndices: number[]) => {
    setColumnsState(previousColumnsState => ({
      ...previousColumnsState,
      columns: newIndices.map(index => previousColumnsState.columns[index]),
    }));
  }, []);

  const selectedColumn: ColumnConfiguration | null =
    columnsState.columns.find(({uuid}) => columnsState.selectedColumnUuid === uuid) ?? null;

  return (
    <ValidationErrorsContext.Provider value={validationErrors}>
      <Container>
        <ColumnList
          columnsState={columnsState}
          setColumnsState={setColumnsState}
          onColumnCreated={handleCreateColumn}
          onColumnsCreated={handleCreateColumns}
          onColumnChange={handleChangeColumn}
          onColumnSelected={handleSelectColumn}
          onColumnRemoved={handleRemoveColumn}
          onColumnReorder={handleReorderColumns}
        />
        {null === selectedColumn ? (
          <ColumnDetailsPlaceholder />
        ) : (
          <ColumnDetails columnConfiguration={selectedColumn} onColumnChange={handleChangeColumn} />
        )}
      </Container>
    </ValidationErrorsContext.Provider>
  );
};

export {ColumnsTab};
export type {ColumnsTabProps};
