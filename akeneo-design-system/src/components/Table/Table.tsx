import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {TableCell} from './TableCell/TableCell';
import {TableHeader} from './TableHeader/TableHeader';
import {TableHeaderCell} from './TableHeaderCell/TableHeaderCell';
import {TableActionCell} from './TableActionCell/TableActionCell';
import {TableRow} from './TableRow/TableRow';
import {SelectableContext} from './SelectableContext';
import {TableBody} from './TableBody/TableBody';
import {TableImageCell} from './TableImageCell/TableImageCell';

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
   * Define if the checkbox should be always displayed or displayed on hover
   * This props should be true when one element is checked
   */
  displayCheckbox?: boolean;

  /**
   * The content of the table
   */
  children?: ReactNode;
};

const Table = ({isSelectable = false, displayCheckbox = false, children, ...rest}: TableProps) => {
  return (
    <SelectableContext.Provider value={{isSelectable, displayCheckbox}}>
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
Table.ImageCell = TableImageCell;

export {Table};
