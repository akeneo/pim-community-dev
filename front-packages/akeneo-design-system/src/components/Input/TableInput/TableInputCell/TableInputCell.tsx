import styled, {css} from 'styled-components';
import React, {Ref} from 'react';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Override} from '../../../../shared';

const TableInputTd = styled.td<{rowTitle: boolean} & AkeneoThemedProps>`
  padding: 0;
  ${({rowTitle}) =>
    rowTitle &&
    css`
      color: ${getColor('brand', 100)};
      padding: 0 10px;
      font-weight: bold;
    `};
`;

type TableInputCellProps = Override<
  React.TdHTMLAttributes<HTMLTableCellElement>,
  {
    rowTitle?: boolean;
  }
>;

const TableInputCell = React.forwardRef<HTMLTableCellElement, TableInputCellProps>(
  ({children, rowTitle = false, ...rest}: TableInputCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    return (
      <TableInputTd rowTitle={rowTitle} ref={forwardedRef} {...rest}>
        {children}
      </TableInputTd>
    );
  }
);

TableInputCell.displayName = 'TableInput.Cell';

export {TableInputCell};
