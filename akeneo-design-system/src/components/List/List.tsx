import React, {ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';

const ListContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const Row = styled.div`
  display: flex;
  gap: 10px;

  &:not(:last-child) {
    border-bottom: 1px solid ${getColor('grey', 60)};
  }
`;
const CellContainer = styled.div<{width: 'auto' | number; rowTitle: boolean} & AkeneoThemedProps>`
  min-height: 54px;
  padding: 17px 0;
  box-sizing: border-box;
  font-size: ${getFontSize('default')};

  ${({rowTitle}) =>
    rowTitle
      ? css`
          color: ${getColor('purple', 100)};
          font-style: italic;
        `
      : css`
          color: ${getColor('grey', 140)};
        `};

  ${({width}) =>
    'auto' === width
      ? css`
          flex: 1;
        `
      : css`
          width: ${width}px;
        `};
`;

const CellAligner = styled.div`
  display: flex;
  align-items: center;
  height: 40px;
`;

type CellProps = {
  width: 'auto' | number;
  rowTitle?: true | undefined;
  extensible?: true | undefined;
} & React.HTMLAttributes<HTMLDivElement>;

const Cell = ({title, width, rowTitle, extensible, children, ...rest}: CellProps) => {
  title = undefined === title && typeof children === 'string' ? children : title;

  return (
    <CellContainer width={width} rowTitle={rowTitle === true} title={title} {...rest}>
      {extensible ? children : <CellAligner>{children}</CellAligner>}
    </CellContainer>
  );
};

type ListProps = {
  /**
   * TODO.
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

List.Row = Row;
List.Cell = Cell;

export {List};
