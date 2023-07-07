import React, {Ref} from 'react';
import styled from 'styled-components';
import {getColor} from '../../../../theme';

const TableInputTh = styled.th`
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: normal;
  padding: 0 10px;
  color: ${getColor('grey', 140)};
  text-align: left;
  font-weight: bold;
  white-space: nowrap;
  min-width: 150px;
  max-width: 250px;
`;

export type TableInputHeaderCellProps = React.TdHTMLAttributes<HTMLTableCellElement>;

const TableInputHeaderCell: React.FC<TableInputHeaderCellProps & {ref?: React.Ref<HTMLTableHeaderCellElement>}> = React.forwardRef<HTMLTableHeaderCellElement, TableInputHeaderCellProps>(
  ({children, ...rest}: TableInputHeaderCellProps, forwardedRef: Ref<HTMLTableHeaderCellElement>) => {
    return (
      <TableInputTh ref={forwardedRef} {...rest}>
        {children}
      </TableInputTh>
    );
  }
);

TableInputHeaderCell.displayName = 'TableInput.HeaderCell';

export {TableInputHeaderCell};
