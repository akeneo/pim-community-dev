import React, {ReactNode, Ref, cloneElement, Children, useContext} from 'react';
import {TableRowProps} from '../TableRow/TableRow';
import {TableContext} from '../TableContext';
import {useDrop} from './useDrop';
import {useDragElementIndex} from './useDragElementIndex';

type TableBodyProps = {
  /**
   * Header rows
   */
  children?: ReactNode;
};

const TableBody = React.forwardRef<HTMLTableSectionElement, TableBodyProps>(
  ({children, ...rest}: TableBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const [draggedElementIndex, onDragStart, onDragEnd] = useDragElementIndex();
    const {isDragAndDroppable} = useContext(TableContext);
    const decoratedChildren = isDragAndDroppable
      ? Children.map(children, (child, rowIndex) => {
          if (!React.isValidElement<TableRowProps>(child)) {
            throw Error('Children of Table.Body should be a valid react element');
          }

          return cloneElement(child, {
            rowIndex,
            draggedElementIndex,
            onDragStart: () => onDragStart(rowIndex),
            onDragEnd,
          });
        })
      : children;

    const rowCount = Children.count(children);
    const [tableId, onDrop, onDragOver] = useDrop(rowCount, draggedElementIndex);

    return (
      <tbody ref={forwardedRef} data-table-id={tableId} onDrop={onDrop} onDragOver={onDragOver} {...rest}>
        {decoratedChildren}
      </tbody>
    );
  }
);

export {TableBody};
export type {TableBodyProps};
