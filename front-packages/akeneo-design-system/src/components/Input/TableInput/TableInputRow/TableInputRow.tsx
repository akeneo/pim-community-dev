import styled from 'styled-components';
import React, {forwardRef, HTMLAttributes, Ref} from 'react';
import {getColor} from '../../../../theme';

const TableInputTr = styled.tr`
  height: 40px;
  & > td {
    border: 1px solid ${getColor('grey', 60)};
    border-right-width: 0;
    border-top-width: 0;
  }
  & > td:first-child {
    position: sticky;
    left: 0;
    z-index: 1;
  }
  & > td:last-child {
    border-right-width: 1px;
  }
`;

const TableInputRow = forwardRef<HTMLTableRowElement, HTMLAttributes<HTMLTableRowElement>>(
  ({children, ...rest}: HTMLAttributes<HTMLTableRowElement>, forwardedRef: Ref<HTMLTableRowElement>) => {
    return (
      <TableInputTr ref={forwardedRef} {...rest}>
        {children}
      </TableInputTr>
    );
  }
);

TableInputRow.displayName = 'TableInput.Row';

export {TableInputRow};
