import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Image} from '../..';

const TableCellContainer = styled.td<{isHighlighted: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  border-bottom: 1px solid ${getColor('grey', 60)};
  padding: 15px 10px;
  max-width: 15vw;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;

  ${({isHighlighted}) =>
    isHighlighted &&
    css`
      color: ${getColor('purple', 100)};
      font-style: italic;
      font-weight: bold;
    `}
`;

const TableCellInnerContainer = styled.div`
  display: flex;
`;

type TableCellProps = {
  /**
   * Define that cell information is important
   */
  isHighlighted?: boolean;

  /**
   * Content of the cell
   */
  children?: ReactNode;
};

const TableCell = React.forwardRef<HTMLTableCellElement, TableCellProps>(
  ({isHighlighted = false, children, ...rest}: TableCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    return (
      <TableCellContainer ref={forwardedRef} isHighlighted={isHighlighted} {...rest}>
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
