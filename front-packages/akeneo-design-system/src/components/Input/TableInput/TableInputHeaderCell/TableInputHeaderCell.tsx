import React, {Ref} from 'react';
import styled from 'styled-components';
import {Override} from '../../../../shared';
import {getColor} from '../../../../theme';

const TableInputTh = styled.th`
  font-weight: normal;
  padding: 0 10px;
  color: ${getColor('grey', 140)};
  text-align: left;
  font-weight: bold;
`;

const TableInputHeaderCell = React.forwardRef<HTMLTableHeaderCellElement, React.TdHTMLAttributes<HTMLTableCellElement>>(
  (
    {children, ...rest}: React.TdHTMLAttributes<HTMLTableCellElement>,
    forwardedRef: Ref<HTMLTableHeaderCellElement>
  ) => {
    return (
      <TableInputTh ref={forwardedRef} {...rest}>
        {children}
      </TableInputTh>
    );
  }
);

TableInputHeaderCell.displayName = 'TableInput.HeaderCell';

export {TableInputHeaderCell};
