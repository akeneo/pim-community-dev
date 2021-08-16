import React, {Children, ReactNode, Ref, useContext} from 'react';
import styled from 'styled-components';
import {getColor} from '../../../../theme';
import {TableInputContext} from '../TableInputContext';
import {TableInputHeaderCellProps} from '../TableInputHeaderCell/TableInputHeaderCell';

const TableInputHeadTr = styled.tr`
  height: 40px;
  background: ${getColor('grey', 40)};
  & > th {
    border: 1px solid ${getColor('grey', 60)};
    border-left-width: 0;

    &:first-child {
      border-left-width: 1px;
      position: sticky;
      left: 0;
      background: ${getColor('grey', 40)};
      z-index: 1;
    }
  }
`;

type TableInputHeaderProps = {
  children?: ReactNode;
};

const TableInputHeader = React.forwardRef<HTMLTableSectionElement, TableInputHeaderProps>(
  ({children, ...rest}: TableInputHeaderProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const {isDragAndDroppable} = useContext(TableInputContext);

    return (
      <thead ref={forwardedRef} {...rest}>
        <TableInputHeadTr>
          {Children.map(children, (child, i) => {
            return isDragAndDroppable && i === 0 && React.isValidElement<TableInputHeaderCellProps>(child)
              ? React.cloneElement(child, {colSpan: 2})
              : child;
          })}
        </TableInputHeadTr>
      </thead>
    );
  }
);

TableInputHeader.displayName = 'TableInput.Header';

export {TableInputHeader};
