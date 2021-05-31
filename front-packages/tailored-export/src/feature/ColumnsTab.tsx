import React, {useState} from 'react';
import styled from 'styled-components';
import {uuid} from 'akeneo-design-system';
import {ValidationError} from '@akeneo-pim-community/shared';
import {createColumn, addColumn, removeColumn, ColumnConfiguration, updateColumn} from './models/ColumnConfiguration';
import {ColumnDetails} from './components/ColumnDetails/ColumnDetails';
import {ColumnList} from './components/ColumnList/ColumnList';
import {ValidationErrorsContext} from './contexts/ValidationErrorsContext';
import {ColumnDetailsPlaceholder} from './components/ColumnDetails/ColumnDetailsPlaceholder';

const MAX_COLUMN_COUNT = 1000;

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

const ColumnsTab = ({columnsConfiguration, validationErrors, onColumnsConfigurationChange}: ColumnsTabProps) => {
  const [selectedColumn, setSelectedColumn] = useState<string | null>(
    columnsConfiguration.length === 0 ? null : columnsConfiguration[0].uuid
  );
  const handleCreateColumn = (newColumnName: string) => {
    const column = createColumn(newColumnName, uuid());
    onColumnsConfigurationChange(addColumn(columnsConfiguration, column));
    setSelectedColumn(column.uuid);
  };
  const handleCreateColumns = (newColumnNames: string[]) => {
    const newColumns = newColumnNames.reduce((existingColumns, newColumnName) => {
      if (existingColumns.length === MAX_COLUMN_COUNT) return existingColumns;

      const columnToAdd = createColumn(newColumnName, uuid());

      return addColumn(existingColumns, columnToAdd);
    }, columnsConfiguration);

    onColumnsConfigurationChange(newColumns);
    setSelectedColumn(newColumns[newColumns.length - 1].uuid);
  };
  const handleRemoveColumn = (columnUuid: string) => {
    onColumnsConfigurationChange(removeColumn(columnsConfiguration, columnUuid));
  };
  const handleSelectColumn = (selectedColumn: string | null) => {
    setSelectedColumn(selectedColumn);
  };
  const handleChangeColumn = (column: ColumnConfiguration) => {
    onColumnsConfigurationChange(updateColumn(columnsConfiguration, column));
  };
  const handleReorderColumns = (newIndices: number[]) => {
    onColumnsConfigurationChange(newIndices.map(index => columnsConfiguration[index]));
  };

  const selectedColumnConfiguration = columnsConfiguration.find(({uuid}) => selectedColumn === uuid) ?? null;

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

export {ColumnsTab, MAX_COLUMN_COUNT};
export type {ColumnsTabProps};
