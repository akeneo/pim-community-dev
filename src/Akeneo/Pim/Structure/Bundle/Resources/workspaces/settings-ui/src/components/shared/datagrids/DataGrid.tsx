import React, {PropsWithChildren} from 'react';
import {Body} from './Body';
import {Column} from './Column';
import {HeaderRow} from './HeaderRow';
import {Row} from './Row';
import {AfterMoveRowHandler, CompareRowDataHandler, DataGridStateProvider} from '../providers';
import {Table, TableContainer} from '../layouts/tables';
import {SearchBar} from '@akeneo-pim-community/shared/src';

type Props<T> = {
  isReorderAllowed?: boolean;
  isReorderActive?: boolean;
  dataSource: T[];
  handleAfterMove: AfterMoveRowHandler<T>;
  compareData: CompareRowDataHandler<T>;
  searchValue?: string;
  onSearch?: (searchValue: string) => void;
};

const DataGrid = <T extends {}>({
  children,
  isReorderAllowed,
  isReorderActive,
  dataSource,
  handleAfterMove,
  compareData,
  searchValue,
  onSearch,
}: PropsWithChildren<Props<T>>) => {
  return (
    <DataGridStateProvider
      isReorderAllowed={isReorderAllowed || false}
      isReorderActive={isReorderActive || false}
      dataSource={dataSource}
      handleAfterMove={handleAfterMove}
      compareData={compareData}
      isFilterable={onSearch !== undefined}
    >
      <TableContainer>
        {
          onSearch !== undefined &&
          <SearchBar count={dataSource.length} searchValue={searchValue === undefined ? '' : searchValue} onSearchChange={onSearch}/>
        }
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
