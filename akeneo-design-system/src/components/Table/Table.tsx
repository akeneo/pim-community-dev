import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {TableCell} from './TableCell/TableCell';
import {TableHeader} from './TableHeader/TableHeader';
import {TableHeaderCell} from './TableHeaderCell/TableHeaderCell';
import {TableActionCell} from './TableActionCell/TableActionCell';
import {TableRow} from './TableRow/TableRow';
import {SelectableContext} from './SelectableContext';
import {TableBody} from './TableBody/TableBody';

const TableContainer = styled.table`
  border-collapse: collapse;
  width: 100%;
`;

type TableProps = {
  /**
   * Define if rows can be selected
   */
  isSelectable?: boolean;

  /**
   *
   */
  amountSelectedRows?: number;

  /**
   * The content of the table
   */
  children?: ReactNode;
};

const Table = ({isSelectable = false, amountSelectedRows, children, ...rest}: TableProps) => {
  if (isSelectable && undefined === amountSelectedRows) {
    throw Error('A selectable table should have the prop "amountSelectedRows"');
  }

  return (
    <SelectableContext.Provider value={{isSelectable, amountSelectedRows}}>
      <TableContainer {...rest}>{children}</TableContainer>
    </SelectableContext.Provider>
  );
};

Table.Header = TableHeader;
Table.HeaderCell = TableHeaderCell;
Table.Body = TableBody;
Table.Row = TableRow;
Table.Cell = TableCell;
Table.ActionCell = TableActionCell;

export {Table};
