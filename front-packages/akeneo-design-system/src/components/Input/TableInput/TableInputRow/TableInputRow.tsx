import styled from "styled-components";
import React, { forwardRef, HTMLAttributes, Ref } from "react";
import { Override } from "../../../../shared";

const TableInputTr = styled.tr`
  height: 40px;
  border-radius: 2px;
  & > * {
    border: 1px solid #c7cbd4;
    border-left-width: 0;
  }
  & > *:first-child {
    border-left-width: 1px;
    border-top-left-radius: 2px;
    border-bottom-left-radius: 2px;
  }
  & > *:last-child {
    border-top-right-radius: 2px;
    border-bottom-right-radius: 2px;
  }
`;

type TableInputRowProps = Override<
  HTMLAttributes<HTMLTableRowElement>,
  {}
>;

const TableInputRow = forwardRef<HTMLTableRowElement, TableInputRowProps>(
  ({children, ...rest}: TableInputRowProps, forwardedRef: Ref<HTMLTableRowElement>) => {
  return <TableInputTr ref={forwardedRef} {...rest}>
    {children}
  </TableInputTr>;
});

TableInputRow.displayName = 'TableInput.Row';

export {TableInputRow};
