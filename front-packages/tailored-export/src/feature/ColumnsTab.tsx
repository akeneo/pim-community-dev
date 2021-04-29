import React, {useState} from 'react';
import styled from 'styled-components';
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
import {uuid} from 'akeneo-design-system';

type ColumnProps = {
  columnsConfiguration: ColumnsConfiguration;

  onColumnsConfigurationChange: (columnsConfiguration: ColumnsConfiguration) => void;
};

const Container = styled.div`
  padding-top: 10px;
  padding-bottom: 40px;
  height: 100%;
  display: flex;
  gap: 20px;
`;

const ColumnsTab = ({columnsConfiguration, onColumnsConfigurationChange}: ColumnProps) => {
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
  );
};

export {ColumnsTab};
export type {ColumnsConfiguration};
