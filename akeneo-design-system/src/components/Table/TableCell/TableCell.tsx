import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Image} from '../..';
import {getBodyStyle} from '../../../typography';

const TableCellContainer = styled.td<{rowTitle: boolean} & AkeneoThemedProps>`
  ${getBodyStyle({
    size: 'regular',
    color: 'grey',
    gradient: 140,
    weight: 'regular',
  })}
  border-bottom: 1px solid ${getColor('grey', 60)};
  padding: 15px 10px;
  max-width: 15vw;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;

  ${props =>
    props.rowTitle &&
    css`
      ${getBodyStyle({
        size: 'regular',
        color: 'brand',
        gradient: 100,
        weight: 'regular',
      })}
      font-style: italic;
    `}
`;

const TableCellInnerContainer = styled.div`
  display: flex;
  align-items: center;
  min-height: 24px;
`;

type TableCellProps = {
  /**
   * Content of the cell.
   */
  children?: ReactNode;

  /**
   * Define the content as title for the row.
   */
  rowTitle?: boolean;
};

const TableCell = React.forwardRef<HTMLTableCellElement, TableCellProps>(
  ({children, rowTitle = false, ...rest}: TableCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    return (
      <TableCellContainer ref={forwardedRef} rowTitle={rowTitle} {...rest}>
        <TableCellInnerContainer>
          {React.Children.map(children, child => {
            if (!React.isValidElement(child) || child.type !== Image) return child;

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
