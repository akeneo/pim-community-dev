import React, {isValidElement, ReactElement, ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {Button, ButtonProps} from '../Button/Button';
import {IconButton} from '../IconButton/IconButton';

const ListContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const CellContainer = styled.div<{width: 'auto' | number} & AkeneoThemedProps>`
  min-height: 54px;
  padding: 17px 0;
  box-sizing: border-box;
  font-size: ${getFontSize('default')};
  color: ${getColor('grey', 140)};
  display: flex;

  ${({width}) =>
  'auto' === width
    ? css`
          flex: 1;
        `
    : css`
          width: ${width}px;
        `};
`;

const TitleCell = styled(CellContainer)`
  color: ${getColor('purple', 100)};
  font-style: italic;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
`;

const ActionCellContainer = styled(CellContainer)`
  opacity: 0;
  display: flex;
  gap: 10px
`;

const RemoveCellContainer = styled(CellContainer)``;

type RemoveCellProps = React.HTMLAttributes<HTMLDivElement>;
const RemoveCell = ({children, ...rest}: RemoveCellProps) => {
  return (
    <RemoveCellContainer width='auto' {...rest}>{children}</RemoveCellContainer>
  );
};

const RowActionContainer = styled.div`
  display: flex;
  margin-left: 30px;
  gap: 10px;
`;

const RowContainer = styled.div<{multiline: boolean} & AkeneoThemedProps>`
  display: flex;
  outline-style: none;
  &:not(:last-child) {
    border-bottom: 1px solid ${getColor('grey', 60)};
  }

  &:hover {
    background-color: ${getColor('grey', 20)};
  }

  &:focus {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }

  &:hover ${ActionCellContainer} {
    opacity: 1;
  }

  ${CellContainer} {
    align-items: ${({multiline}) => multiline ? 'start' : 'center'};
  }

  ${TitleCell}, ${RemoveCellContainer} {
    height: ${({multiline}) => multiline ? '74px' : 'auto'};
    align-items: center;
  }
`;

const RowContentContainer = styled.div`
  display: flex;
  gap: 10px;
  flex: 1;
  min-width: 0;
`;

type RowProps = Override<React.HTMLAttributes<HTMLDivElement>, {
  /**
   * Define if line contain multiline content
   */
  multiline?: boolean;
}>;

const Row = ({children, multiline = false}: RowProps) => {
  const actionCellChild: ReactElement[] = [];
  const cells: ReactNode[] = [];

  React.Children.forEach(children, child => {
    if (isValidElement(child) && (child.type === RemoveCell || child.type === ActionCell)) {
      actionCellChild.push(child);
    } else {
      cells.push(child);
    }
  });

  return (
   <RowContainer multiline={multiline} tabIndex={0}>
     <RowContentContainer>{cells}</RowContentContainer>
     {actionCellChild.length > 0 && (<RowActionContainer>{actionCellChild}</RowActionContainer>)}
   </RowContainer>
  );
};


type CellProps = Override<React.HTMLAttributes<HTMLDivElement>, {
  /**
   * The width of the cell.
   */
  width: 'auto' | number;
}>;

const Cell = ({title, width, children, ...rest}: CellProps) => {
  title = undefined === title && typeof children === 'string' ? children : title;

  return (
    <CellContainer width={width} title={title} {...rest}>
      {children}
    </CellContainer>
  );
};

type ActionCellProps = React.HTMLAttributes<HTMLDivElement>;
const ActionCell = ({children, ...rest}: ActionCellProps) => {
  const decoratedChildren = React.Children.map(children, child => {
    if (React.isValidElement<ButtonProps>(child) && (child.type === Button || child.type === IconButton)) {
      return React.cloneElement(child, {
        size: 'small',
        ghost: true,
        level: 'tertiary'
      });
    }

    return child;
  });

  return (
    <ActionCellContainer {...rest}>
      {decoratedChildren}
    </ActionCellContainer>
  );
};

type ListProps = {
  /**
   * The rows of the list
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const List = ({children, ...rest}: ListProps) => {
  return <ListContainer {...rest}>{children}</ListContainer>;
};

Row.displayName = 'List.Row';
Cell.displayName = 'List.Cell';
TitleCell.displayName = 'List.Title';
ActionCell.displayName = 'List.ActionCell';
RemoveCell.displayName = 'List.RemoveCell';

List.Row = Row;
List.Cell = Cell;
List.Title = TitleCell;
List.ActionCell = ActionCell;
List.RemoveCell = RemoveCell;

export {List};
