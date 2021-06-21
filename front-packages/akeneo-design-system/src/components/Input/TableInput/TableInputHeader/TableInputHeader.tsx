import React, {ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {getColor} from '../../../../theme';

const TableInputHead = styled.thead``;

const TableInputHeadTr = styled.tr`
  height: 40px;
  background: ${getColor('grey', 40)};
  & > * {
    border: 1px solid ${getColor('grey', 60)};
    border-left-width: 0;
  }
  & > *:first-child {
    border-left-width: 1px;
    position: sticky;
    left: 0;
    background: ${getColor('grey', 40)};
    z-index: 1;
  }
`;

type TableInputHeaderProps = {
  children?: ReactNode;
};

const TableInputHeader = React.forwardRef<HTMLTableSectionElement, TableInputHeaderProps>(
  ({children, ...rest}: TableInputHeaderProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    return (
      <TableInputHead ref={forwardedRef} {...rest}>
        <TableInputHeadTr>{children}</TableInputHeadTr>
      </TableInputHead>
    );
  }
);

TableInputHeader.displayName = 'TableInput.Header';

export {TableInputHeader};
