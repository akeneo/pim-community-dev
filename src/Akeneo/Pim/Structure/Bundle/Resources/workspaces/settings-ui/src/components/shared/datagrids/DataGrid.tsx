import React, {PropsWithChildren} from 'react';
import {Body} from './Body';
import {Column} from './Column';
import {HeaderRow} from './HeaderRow';
import {Row} from './Row';
import {AfterMoveRowHandler, CompareRowDataHandler, DataGridStateProvider} from '../providers';
import {Table, TableContainer} from '../layouts/tables';

type Props<T> = {
  isDraggable?: boolean;
  dataSource: T[];
  handleAfterMove: AfterMoveRowHandler<T>;
  compareData: CompareRowDataHandler<T>;
};

const DataGrid = <T extends {}>({
  children,
  isDraggable,
  dataSource,
  handleAfterMove,
  compareData,
}: PropsWithChildren<Props<T>>) => {
  return (
    <DataGridStateProvider
      isDraggable={isDraggable || false}
      dataSource={dataSource}
      handleAfterMove={handleAfterMove}
      compareData={compareData}
    >
      <TableContainer>
        <Table>{children}</Table>
      </TableContainer>
    </DataGridStateProvider>
  );
};


DataGrid.Body = Body;
DataGrid.Column = Column;
DataGrid.HeaderRow = HeaderRow;
DataGrid.Row = Row;

export {DataGrid};
