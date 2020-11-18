import React, {ReactNode, SyntheticEvent, useContext, useEffect, useState} from 'react';
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
  amountSelectedRows?: number;
  children?: ReactNode;
};

const SelectableContext = React.createContext<{
  isSelectable: boolean;
  amountSelectedRows?: number;
}>({
  isSelectable: false,
  amountSelectedRows: undefined,
});

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

type TableHeaderProps = {
  isSelectable: boolean;
  children?: ReactNode;
};

const HeaderRowContainer = styled.tr``;

Table.Header = ({children, ...rest}: TableHeaderProps) => {
  const {isSelectable} = useContext(SelectableContext);

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

const RowContainer = styled.tr<{isSelected: boolean} & AkeneoThemedProps>`
  min-height: 54px;
  border-bottom: 1px solid ${getColor('grey', 60)};
  ${props =>
    props.isSelected &&
    css`
      background-color: ${getColor('blue', 20)};
    `};
  &:hover {
    background-color: ${getColor('grey', 20)};
  }

  &:hover td {
    opacity: 1;
  }
`;

const CheckboxContainer = styled.td<{isVisible: boolean}>`
  opacity: ${props => (props.isVisible ? 1 : 0)};
`;

Table.Row = ({isSelected, onSelectToggle, children, ...rest}: TableRowProps) => {
  const {isSelectable, amountSelectedRows} = useContext(SelectableContext);

  if (isSelectable && undefined === isSelected) {
    throw Error('A row in a selectable table should have the prop "isSelected"');
  }
  if (isSelectable && undefined === onSelectToggle) {
    throw Error('A row in a selectable table should have the prop "onSelectToggle"');
  }

  const isCheckboxVisible = amountSelectedRows > 0;

  const handleCheckboxChange = () => {
    undefined !== onSelectToggle && onSelectToggle(!isSelected);
  };

  return (
    <RowContainer isSelected={isSelected} {...rest}>
      {isSelectable && undefined !== isSelected &&
        <CheckboxContainer isVisible={isCheckboxVisible}>
          <Checkbox checked={isSelected} onChange={handleCheckboxChange} />
        </CheckboxContainer>
      }
      {children}
    </RowContainer>
  );
};

Table.Cell = styled.td`
  color: ${getColor('grey', 140)};
  padding: 0 10px;
`;

export {Table};
