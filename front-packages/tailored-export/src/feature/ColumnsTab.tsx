import React, {useState} from 'react';
import styled from 'styled-components';
import {uuid} from 'akeneo-design-system';
import {ValidationError} from '@akeneo-pim-community/shared';
import {
  ColumnsConfiguration,
  createColumn,
  addColumn,
  removeColumn,
  ColumnConfiguration,
  updateColumn,
} from './models/ColumnConfiguration';
import {ColumnDetails} from './components/ColumnDetails/ColumnDetails';
import {ColumnList} from './components/ColumnList/ColumnList';
import {ValidationErrorsContext} from './contexts/ValidationErrorsContext';

type ColumnProps = {
  columnsConfiguration: ColumnsConfiguration;
  validationErrors: ValidationError[];
  onColumnsConfigurationChange: (columnsConfiguration: ColumnsConfiguration) => void;
};

const Container = styled.div`
  padding-top: 10px;
  height: 100%;
  display: flex;
  gap: 20px;
`;

const ColumnsTab = ({columnsConfiguration, validationErrors, onColumnsConfigurationChange}: ColumnProps) => {
  const [selectedColumn, setSelectedColumn] = useState<string | null>(
    columnsConfiguration.length === 0 ? null : columnsConfiguration[0].uuid
  );
  const handleCreateColumn = (newColumnName: string) => {
    const column = createColumn(newColumnName, uuid());
    onColumnsConfigurationChange(addColumn(columnsConfiguration, column));
    setSelectedColumn(column.uuid);
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

  const selectedColumnConfiguration = columnsConfiguration.find(({uuid}) => selectedColumn === uuid) ?? null;

  return (
    <ValidationErrorsContext.Provider value={validationErrors}>
      <Container>
        <ColumnList
          columnsConfiguration={columnsConfiguration}
          selectedColumn={selectedColumnConfiguration}
          onColumnCreated={handleCreateColumn}
          onColumnChange={handleChangeColumn}
          onColumnSelected={handleSelectColumn}
          onColumnRemoved={handleRemoveColumn}
        />
        <ColumnDetails
          columnConfiguration={selectedColumnConfiguration}
          noColumns={columnsConfiguration.length === 0}
          onColumnChange={handleChangeColumn}
        />
      </Container>
    </ValidationErrorsContext.Provider>
  );
};

export {ColumnsTab};
export type {ColumnsConfiguration};
