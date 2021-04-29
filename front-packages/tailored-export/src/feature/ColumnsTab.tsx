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
import {ColumnDetails} from 'feature/components/ColumnDetails/ColumnDetails';
import {ColumnList} from 'feature/components/ColumnList/ColumnList';

type ColumnProps = {
  columnsConfiguration: ColumnsConfiguration;
};

const Container = styled.div`
  padding-top: 10px;
  padding-bottom: 40px;
  height: 100%;
  display: flex;
  gap: 20px;
`;

const ColumnsTab = ({columnsConfiguration}: ColumnProps) => {
  const [columns, setColumns] = useState<ColumnsConfiguration>(columnsConfiguration);
  const [selectedColumn, setSelectedColumn] = useState<string | null>(
    columnsConfiguration.length === 0 ? null : columnsConfiguration[0].uuid
  );
  const handleCreateColumn = (newColumnName: string) => {
    const column = createColumn(newColumnName);
    setColumns(columns => addColumn(columns, column));
    setSelectedColumn(column.uuid);
  };
  const handleRemoveColumn = (columnUuid: string) => {
    setColumns(columns => removeColumn(columns, columnUuid));
  };
  const handleSelectColumn = (selectedColumn: string | null) => {
    setSelectedColumn(selectedColumn);
  };
  const handleChangeColumn = (column: ColumnConfiguration) => {
    setColumns(columns => updateColumn(columns, column));
  };

  const selectedColumnConfiguration = columns.find(({uuid}) => selectedColumn === uuid) ?? null;

  return (
    <Container>
      <ColumnList
        columnsConfiguration={columns}
        selectedColumn={selectedColumnConfiguration}
        onColumnCreated={handleCreateColumn}
        onColumnChange={handleChangeColumn}
        onColumnSelected={handleSelectColumn}
        onColumnRemoved={handleRemoveColumn}
      />
      <ColumnDetails
        columnConfiguration={selectedColumnConfiguration}
        noColumns={columns.length === 0}
        onColumnChange={handleChangeColumn}
      />
    </Container>
  );
};

export {ColumnsTab};
export type {ColumnsConfiguration};
