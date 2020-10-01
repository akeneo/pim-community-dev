import React, {PropsWithChildren} from 'react';
import {Body} from './Body';
import {Column} from './Column';
import {HeaderRow} from './HeaderRow';
import {Row} from './Row';
import {AfterMoveRowHandler, CompareRowDataHandler, DataGridStateProvider} from '../providers';
import {Table, TableContainer} from '../layouts/tables';

type Props<T> = {
  isReorderAllowed?: boolean;
  isReorderActive?: boolean;
  dataSource: T[];
  handleAfterMove: AfterMoveRowHandler<T>;
  compareData: CompareRowDataHandler<T>;
  isFilterable?: boolean;
};

const DataGrid = <T extends {}>({
  children,
  isReorderAllowed,
  isReorderActive,
  dataSource,
  handleAfterMove,
  compareData,
  isFilterable,
}: PropsWithChildren<Props<T>>) => {
  return (
    <DataGridStateProvider
      isReorderAllowed={isReorderAllowed || false}
      isReorderActive={isReorderActive || false}
      dataSource={dataSource}
      handleAfterMove={handleAfterMove}
      compareData={compareData}
      isFilterable={isFilterable === true}
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
