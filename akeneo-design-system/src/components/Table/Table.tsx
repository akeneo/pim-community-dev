import React, {ReactNode, SyntheticEvent, useContext} from 'react';
import styled, {css} from 'styled-components';
import {ArrowDownIcon, ArrowUpIcon} from '../../icons';
import {Checkbox} from '..';
import {AkeneoThemedProps, getColor} from '../../theme';

const TableContainer = styled.table`
  border-collapse: collapse;
  width: 100%;
`;

type TableProps = {
  isSelectable: boolean;
  children?: ReactNode;
};

const TableIsSelectableContext = React.createContext(false);

/**
 * TODO
 */
const Table = ({isSelectable = false, children, ...rest}: TableProps) => {
  return (
    <TableIsSelectableContext.Provider value={isSelectable}>
      <TableContainer {...rest}>{children}</TableContainer>
    </TableIsSelectableContext.Provider>
  );
};

type TableHeaderProps = {
  isSelectable: boolean;
  children?: ReactNode;
};

const HeaderRowContainer = styled.tr``;

Table.Header = ({children, ...rest}: TableHeaderProps) => {
  const isSelectable = useContext(TableIsSelectableContext);

  return (
    <thead>
      <HeaderRowContainer {...rest}>
        {isSelectable && <th />}
        {children}
      </HeaderRowContainer>
    </thead>
  );
};

export enum TableSortDirection {
  DESC = 'descending',
  ASC = 'ascending',
  NONE = 'none',
}

type TableHeaderCellProps = {
  sortable: boolean;
  onDirectionChange?: (direction: TableSortDirection) => {};
  direction?: TableSortDirection;
  children?: ReactNode;
};

const HeaderCellContainer = styled.th<{sortable: boolean; isSorted: boolean} & AkeneoThemedProps>`
  background: linear-gradient(to top, #67768a 1px, white 0px);
  height: 44px;
  text-align: left;
  color: ${props => (props.isSorted ? getColor('purple', 100) : getColor('grey', 100))};

  ${props =>
    props.sortable &&
    css`
      cursor: pointer;
    `};
`;

const HeaderCellContentContainer = styled.span`
  color: ${getColor('grey', 140)};
  padding: 0 10px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
`;

Table.HeaderCell = ({sortable, onDirectionChange, direction, children, ...rest}: TableHeaderCellProps) => {
  if (sortable && (onDirectionChange === undefined || direction === undefined)) {
    throw Error('Sortable header should provide onDirectionChange and direction props');
  }

  const handleClick = () => {
    if (!sortable || onDirectionChange === undefined) return;

    switch (direction) {
      case TableSortDirection.ASC:
        onDirectionChange(TableSortDirection.DESC);
        break;
      case TableSortDirection.DESC:
      case TableSortDirection.NONE:
        onDirectionChange(TableSortDirection.ASC);
        break;
    }
  };

  return (
    <HeaderCellContainer
      isSorted={direction !== TableSortDirection.NONE}
      sortable={sortable}
      aria-sort={direction}
      onClick={handleClick}
      {...rest}
    >
      <HeaderCellContentContainer>{children}</HeaderCellContentContainer>
      {sortable &&
        (direction == TableSortDirection.DESC || direction == TableSortDirection.NONE ? (
          <ArrowDownIcon size={14} />
        ) : (
          <ArrowUpIcon size={14} />
        ))}
    </HeaderCellContainer>
  );
};

Table.Body = styled.tbody``;

type TableRowProps = {
  children?: ReactNode;
  onSelectToggle?: (isSelected: boolean) => {};
  isSelected?: boolean;
  onClick: (event: SyntheticEvent) => {};
};

Table.Row = ({isSelected, onSelectToggle, children, ...rest}: TableRowProps) => {
  const isSelectable = useContext(TableIsSelectableContext);
  if (isSelectable && (isSelected === undefined || onSelectToggle === undefined)) {
    throw Error('Selectable row should provide an OnSelectToggle');
  }

  const handleSelect = () => {
    if (!isSelectable || onSelectToggle === undefined) return;

    onSelectToggle(!isSelected);
  };

  return (
    <tr {...rest}>
      {isSelectable && isSelected !== undefined && (
        <td>
          <Checkbox checked={isSelected} onChange={handleSelect} />
        </td>
      )}
      {children}
    </tr>
  );
};

Table.Cell = styled.td``;

export {Table};
