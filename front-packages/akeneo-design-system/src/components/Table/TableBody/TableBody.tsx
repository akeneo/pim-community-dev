import React, {Ref, cloneElement, Children, useContext, ReactElement} from 'react';
import {TableRowProps} from '../TableRow/TableRow';
import {TableContext} from '../TableContext';
import {useDrop} from '../../../hooks/useDrop';
import {useDragElementIndex} from '../../../hooks/useDragElementIndex';

type TableBodyChild = TableBodyChild[] | ReactElement<TableRowProps> | boolean | undefined;

type TableBodyProps = {
  /**
   * Header rows
   */
  children?: TableBodyChild;
};

const TableBody: React.FC<TableBodyProps & {ref?: React.Ref<HTMLTableSectionElement>}> = React.forwardRef<HTMLTableSectionElement, TableBodyProps>(
  ({children, ...rest}: TableBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const [draggedElementIndex, onDragStart, onDragEnd] = useDragElementIndex();
    const {isDragAndDroppable, onReorder} = useContext(TableContext);
    const decoratedChildren = isDragAndDroppable
      ? Children.map(children, (child, rowIndex) => {
          if (!React.isValidElement<TableRowProps>(child)) {
            return null;
          }

          return cloneElement(child, {
            rowIndex,
            draggable: rowIndex === draggedElementIndex,
            onDragStart,
            onDragEnd,
          });
        })
      : children;

    const rowCount = Children.count(children);
    const [tableId, onDrop, onDragOver] = useDrop(rowCount, draggedElementIndex, onReorder);

    return (
      <tbody ref={forwardedRef} data-table-id={tableId} onDrop={onDrop} onDragOver={onDragOver} {...rest}>
        {decoratedChildren}
      </tbody>
    );
  }
);

export {TableBody};
export type {TableBodyProps};
