import React, {ReactNode, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {Image} from '../..';
import {useSkeleton} from '../../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../../Skeleton/Skeleton';

const TableCellContainer = styled.td<{rowTitle: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
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
      color: ${getColor('brand', 100)};
      font-style: italic;
    `}
`;

const TableCellInnerContainer = styled.div<SkeletonProps>`
  display: flex;
  min-height: 24px;

  ${applySkeletonStyle()}
`;

type TableCellProps = {
  /**
   * Content of the cell
   */
  children?: ReactNode;
  /**
   * Define the content as title for the row
   */
  rowTitle?: boolean;
};

const TableCell = React.forwardRef<HTMLTableCellElement, TableCellProps>(
  ({children, rowTitle = false, ...rest}: TableCellProps, forwardedRef: Ref<HTMLTableCellElement>) => {
    const skeleton = useSkeleton();

    return (
      <TableCellContainer ref={forwardedRef} rowTitle={rowTitle} {...rest}>
        <TableCellInnerContainer skeleton={skeleton}>
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
