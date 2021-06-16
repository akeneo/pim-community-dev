import React, { ReactNode, Ref } from "react";
import styled from "styled-components";

const TableInputHead = styled.thead``;

const TableInputHeadTr = styled.tr`
  height: 40px;
  background: #f0f1f3;
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

type TableInputHeaderProps = {
  children?: ReactNode;
};

const TableInputHeader = React.forwardRef<HTMLTableSectionElement, TableInputHeaderProps>(
  ({children, ...rest}: TableInputHeaderProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
  return <TableInputHead ref={forwardedRef} {...rest}>
    <TableInputHeadTr>
      {children}
    </TableInputHeadTr>
  </TableInputHead>;
});

TableInputHeader.displayName = 'TableInput.Header';

export {TableInputHeader};
