import styled from 'styled-components';
import React, {ReactNode, Ref} from 'react';
import {getColor} from '../../../../theme';

const TableInputTbody = styled.tbody`
  & > * > * {
    background: ${getColor('white')};
  }
  & > *:nth-child(2n) > * {
    background: ${getColor('grey', 20)};
  }
`;

type TableInputBodyProps = {
  children?: ReactNode;
};

const TableInputBody = React.forwardRef<HTMLTableSectionElement, TableInputBodyProps>(
  ({children, ...rest}: TableInputBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    return (
      <TableInputTbody ref={forwardedRef} {...rest}>
        {children}
      </TableInputTbody>
    );
  }
);

TableInputBody.displayName = 'TableInput.Body';

export {TableInputBody};
