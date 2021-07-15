import styled, {css} from 'styled-components';
import React, {Ref} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';

const TableInputTd = styled.td<{rowTitle: boolean; highlighted: boolean; inError: boolean} & AkeneoThemedProps>`
  padding: 0;
  min-width: 150px;
  max-width: 250px;

  ${({rowTitle}) =>
    rowTitle &&
    css`
      color: ${getColor('brand', 100)};
      padding: 0 10px;
      font-weight: bold;
    `}

  ${({highlighted, inError}) =>
    highlighted &&
    !inError &&
    css`
      box-shadow: 0 0 0 1px ${getColor('green', 80)} !important;
      border-color: ${getColor('green', 80)} !important;
      background: ${getColor('green', 10)} !important;
    `}
    
  ${({inError}) =>
    inError &&
    css`
      box-shadow: 0 0 0 1px ${getColor('red', 80)} !important;
      border-color: ${getColor('red', 80)} !important;
      background: ${getColor('red', 10)} !important;
    `}
`;

type TableInputCellProps = Override<
  React.TdHTMLAttributes<HTMLTableCellElement>,
  {
    rowTitle?: boolean;
    highlighted?: boolean;
    inError?: boolean;
  }
>;

const TableInputCell = React.forwardRef<HTMLTableCellElement, TableInputCellProps>(
  (
    {children, rowTitle = false, highlighted = false, inError = false, ...rest}: TableInputCellProps,
    forwardedRef: Ref<HTMLTableCellElement>
  ) => {
    return (
      <TableInputTd rowTitle={rowTitle} highlighted={highlighted} inError={inError} ref={forwardedRef} {...rest}>
        {children}
      </TableInputTd>
    );
  }
);

TableInputCell.displayName = 'TableInput.Cell';

export {TableInputCell};
