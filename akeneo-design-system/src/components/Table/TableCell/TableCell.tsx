import React, {ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {getColor} from '../../../theme';
import {Image} from '../..';

const TableCellContainer = styled.td`
  color: ${getColor('grey', 140)};
  border-bottom: 1px solid ${getColor('grey', 60)};
  padding: 15px 10px;
  max-width: 15vw;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;
`;

const TableCellInnerContainer = styled.div`
  display: flex;
`;

type TableCellProps = {
  /**
   * Content of the cell
   */
  children?: ReactNode;
};

const TableCell = React.forwardRef<HTMLTableCellElement, TableCellProps>(
  ({children, ...rest}: TableCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    return (
      <TableCellContainer ref={forwardedRef} {...rest}>
        <TableCellInnerContainer>
          {React.Children.map(children, child => {
            if (!React.isValidElement(child) || child.type !== Image) return children;

            return React.cloneElement(child, {
              width: 44,
              height: 44,
            });
          })}
        </TableCellInnerContainer>
      </TableCellContainer>
    );
  }
);

export {TableCell};
