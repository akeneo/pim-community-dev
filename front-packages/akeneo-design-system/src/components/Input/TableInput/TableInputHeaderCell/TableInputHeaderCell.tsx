import React, { Ref } from "react";
import styled from "styled-components";
import { Override } from "../../../../shared";

const TableInputTh = styled.th`
  font-weight: normal;
  padding: 0 15px;
  color: #11324d;
  text-align: left;
`;

type TableInputHeaderCellProps = Override<
  React.TdHTMLAttributes<HTMLTableCellElement>,
  {}
>;

const TableInputHeaderCell = React.forwardRef<HTMLTableHeaderCellElement, TableInputHeaderCellProps>(
  ({children, ...rest}: TableInputHeaderCellProps, forwardedRef: Ref<HTMLTableHeaderCellElement>) => {
  return <TableInputTh ref={forwardedRef} {...rest}>
    {children}
  </TableInputTh>;
});

TableInputHeaderCell.displayName = 'TableInput.HeaderCell';

export {TableInputHeaderCell};
