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
  children?: ReactNode;
};

const SelectableContext = React.createContext({
  isSelectable: false,
  count: 0,
  setCount: count => {},
});

/**
 * TODO
 */
const Table = ({isSelectable = false, children, ...rest}: TableProps) => {
  const [selectedCount, setSelectedCount] = useState(0);

  return (
    <SelectableContext.Provider value={{isSelectable, count: selectedCount, setCount: setSelectedCount}}>
      <Counter />
      <TableContainer {...rest}>{children}</TableContainer>
    </SelectableContext.Provider>
  );
};

const Counter = () => {
  const context = useContext(SelectableContext);
  return <div>{context.count}</div>;
};

type TableHeaderProps = {
  isSelectable: boolean;
  children?: ReactNode;
};

const HeaderRowContainer = styled.tr``;

Table.Header = ({children, ...rest}: TableHeaderProps) => {
  const selectableContext = useContext(SelectableContext);

  return (
    <thead>
      <HeaderRowContainer {...rest}>
        {selectableContext.isSelectable && <th />}
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
  const selectableContext = useContext(SelectableContext);
  const isSelectable = selectableContext.isSelectable;
  if (isSelectable && (isSelected === undefined || onSelectToggle === undefined)) {
    throw Error('Selectable row should provide an OnSelectToggle');
  }

  const [previousIsSelected, setPreviousIsSelected] = useState(false);

  useEffect(() => {
    if (isSelected !== undefined && previousIsSelected !== isSelected) {
      selectableContext.setCount(count => (isSelected ? count + 1 : count - 1));
      setPreviousIsSelected(isSelected);
    }

    return () => {
      //selectableContext.setCount(count => (isSelected ? count - 1 : count));
    };
  }, [isSelected]);

  const handleSelect = () => {
    if (!isSelectable || onSelectToggle === undefined) return;

    onSelectToggle(!isSelected);
  };

  return (
    <RowContainer isSelected={isSelected} {...rest}>
      {isSelectable && isSelected !== undefined && (
        <CheckboxContainer isVisible={selectableContext.count > 0}>
          <Checkbox checked={isSelected} onChange={handleSelect} />
        </CheckboxContainer>
      )}
      {children}
    </RowContainer>
  );
};

Table.Cell = styled.td`
  color: ${getColor('grey', 140)};
  padding: 0 10px;
`;

export {Table};
