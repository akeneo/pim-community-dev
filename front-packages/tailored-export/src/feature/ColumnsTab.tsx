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
  const [columnsConfiguration, setColumnsConfiguration] = useState<ColumnConfiguration[]>(initial);
  const [selectedColumn, setSelectedColumn] = useState<string | null>(
    columnsConfiguration.length === 0 ? null : columnsConfiguration[0].uuid
  );

  useEffect(() => {
    onColumnsConfigurationChange(columnsConfiguration);
  }, [onColumnsConfigurationChange, columnsConfiguration]);

  const handleCreateColumn = useCallback((newColumnName: string) => {
    const column = createColumn(newColumnName, uuid());
    setColumnsConfiguration(columnsConfiguration => addColumn(columnsConfiguration, column));
    setSelectedColumn(column.uuid);
  }, []);

  const handleCreateColumns = useCallback((newColumnNames: string[]) => {
    setColumnsConfiguration(columnsConfiguration => {
      const newColumns = newColumnNames.reduce((existingColumns, newColumnName) => {
        if (existingColumns.length === MAX_COLUMN_COUNT) return existingColumns;
        const columnToAdd = createColumn(newColumnName, uuid());
        return addColumn(existingColumns, columnToAdd);
      }, columnsConfiguration);

      setSelectedColumn(newColumns[newColumns.length - 1].uuid);

      return newColumns;
    });
  }, []);

  const handleRemoveColumn = useCallback((columnUuid: string) => {
    setColumnsConfiguration(columnsConfiguration => removeColumn(columnsConfiguration, columnUuid));
  }, []);

  const handleSelectColumn = useCallback((selectedColumn: string | null) => {
    setSelectedColumn(selectedColumn);
  }, []);

  const handleChangeColumn = useCallback((column: ColumnConfiguration) => {
    setColumnsConfiguration(columnsConfiguration => updateColumn(columnsConfiguration, column));
  }, []);

  const handleReorderColumns = useCallback((newIndices: number[]) => {
    setColumnsConfiguration(columnsConfiguration => newIndices.map(index => columnsConfiguration[index]));
  }, []);

  const selectedColumnConfiguration = columnsConfiguration.find(({uuid}) => selectedColumn === uuid) ?? null;

  const handleFocusNext = useCallback(() => {
    setColumnsConfiguration(columnsConfiguration => {
      setSelectedColumn(selectedColumn => {
        const currentColumnIndex = columnsConfiguration.findIndex(({uuid}) => selectedColumn === uuid);
        const nextColumn = columnsConfiguration[currentColumnIndex + 1];

        return undefined === nextColumn ? null : nextColumn.uuid;
      });

      return columnsConfiguration;
    });
  }, []);

  return (
    <ValidationErrorsContext.Provider value={validationErrors}>
      <Container>
        <ColumnList
          columnsConfiguration={columnsConfiguration}
          selectedColumn={selectedColumnConfiguration}
          onColumnCreated={handleCreateColumn}
          onColumnsCreated={handleCreateColumns}
          onColumnChange={handleChangeColumn}
          onColumnSelected={handleSelectColumn}
          onColumnRemoved={handleRemoveColumn}
          onColumnReorder={handleReorderColumns}
          onFocusNext={handleFocusNext}
        />
        {null === selectedColumnConfiguration ? (
          <ColumnDetailsPlaceholder />
        ) : (
          <ColumnDetails columnConfiguration={selectedColumnConfiguration} onColumnChange={handleChangeColumn} />
        )}
      </Container>
    </ValidationErrorsContext.Provider>
  );
};

export {ColumnsTab};
export type {ColumnsTabProps};
