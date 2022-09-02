import styled, {css} from 'styled-components';
import React, {ReactNode} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';
import {highlightCell} from '../shared/highlightCell';

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

  ${highlightCell};
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
