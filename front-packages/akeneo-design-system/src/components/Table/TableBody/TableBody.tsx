import React, {ReactNode, Ref, cloneElement, Children, useContext} from 'react';
import {TableRowProps} from '../TableRow/TableRow';
import {useId} from '../../../hooks';
import {TableContext} from '../TableContext';
import {useDrop} from './useDrop';

type TableBodyProps = {
  /**
   * Header rows
   */
  children?: ReactNode;
};

const TableBody = React.forwardRef<HTMLTableSectionElement, TableBodyProps>(
  ({children, ...rest}: TableBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const {isDragAndDroppable, onReorder} = useContext(TableContext);
    const tableId = useId('table_');
    const [handleDrop, handleDragOver] = useDrop(tableId, Children.count(children), onReorder);

    const decoratedChildren = isDragAndDroppable
      ? Children.map(children, (child, index) => {
          if (!React.isValidElement<TableRowProps>(child)) {
            throw Error('Children of Table.Body should be a valid react element');
          }

          return cloneElement(child, {
            rowIndex: index,
          });
        })
      : children;

    return (
      <tbody
        ref={forwardedRef}
        data-table-id={tableId}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        {...rest}
      >
        {decoratedChildren}
      </tbody>
    );
  }
);

export {TableBody};
export type {TableBodyProps};
