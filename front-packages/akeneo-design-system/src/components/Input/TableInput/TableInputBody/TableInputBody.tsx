import styled from "styled-components";
import React, { ReactNode, Ref } from "react";

const TableInputTbody = styled.tbody`
  & > * {
    background: #ffffff;
  }
  & > *:nth-child(2n) {
    background: #f6f7fb;
  }
`;

type TableInputBodyProps = {
  children?: ReactNode;
};

const TableInputBody = React.forwardRef<HTMLTableSectionElement, TableInputBodyProps>(
  ({children, ...rest}: TableInputBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
  return <TableInputTbody ref={forwardedRef} {...rest}>
    {children}
  </TableInputTbody>;
});

TableInputBody.displayName = 'TableInput.Body';

export {TableInputBody}
