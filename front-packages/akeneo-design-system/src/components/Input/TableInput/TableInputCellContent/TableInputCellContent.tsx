import styled, {css} from 'styled-components';
import React, {ReactNode} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';

const TableInputCellContainer = styled.div<
  {rowTitle: boolean; highlighted: boolean; inError: boolean} & AkeneoThemedProps
>`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  ${({rowTitle}) =>
    rowTitle &&
    css`
      color: ${getColor('brand', 100)};
      font-weight: bold;
    `}
  padding: 0 10px;
  height: 39px;
  margin-right: 1px;

  ${({highlighted, inError}) =>
    highlighted &&
    !inError &&
    css`
      background: ${getColor('green', 10)};
      box-shadow: 0 0 0 1px ${getColor('green', 80)};
    `};

  ${({inError}) =>
    inError &&
    css`
      background: ${getColor('red', 10)};
      box-shadow: 0 0 0 1px ${getColor('red', 80)};
    `};
`;

type TableInputCellContentProps = Override<
  React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement>,
  {
    rowTitle?: boolean;
    highlighted?: boolean;
    inError?: boolean;
    children?: ReactNode;
  }
>;

const TableInputCellContent = ({
  children,
  rowTitle = false,
  highlighted = false,
  inError = false,
  ...rest
}: TableInputCellContentProps) => {
  return (
    <TableInputCellContainer {...rest} highlighted={highlighted} inError={inError} rowTitle={rowTitle}>
      {children}
    </TableInputCellContainer>
  );
};

TableInputCellContent.displayName = 'TableInput.CellContent';

export {TableInputCellContent};
