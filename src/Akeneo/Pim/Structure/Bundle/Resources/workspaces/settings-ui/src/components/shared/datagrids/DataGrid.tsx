import React, {Children, isValidElement, PropsWithChildren} from 'react';
import {Body} from './Body';
import {Cell} from './Cell';
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
  isReorderAllowed = false,
  isReorderActive = false,
  dataSource,
  handleAfterMove,
  compareData,
  isFilterable,
}: PropsWithChildren<Props<T>>) => {
  return (
    <DataGridStateProvider
      isReorderAllowed={isReorderAllowed}
      isReorderActive={isReorderActive}
      isFilterable={isFilterable === true}
      dataSource={dataSource}
      handleAfterMove={handleAfterMove}
      compareData={compareData}
    >
      <TableContainer>
        <Table>
          {Children.map(children, child => {
            if (isValidElement(child) && child.type === HeaderRow) {
              return React.cloneElement(child, {
                isDraggable: isReorderAllowed,
              });
            }

            return child;
          })}
        </Table>
      </TableContainer>
    </DataGridStateProvider>
  );
};

DataGrid.Body = Body;
DataGrid.Cell = Cell;
DataGrid.HeaderRow = HeaderRow;
DataGrid.Row = Row;

export {DataGrid};
