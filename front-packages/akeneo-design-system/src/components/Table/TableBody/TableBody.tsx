import React, {ReactNode, Ref, SyntheticEvent, cloneElement, Children, useState, useContext} from 'react';
import {TableRowProps} from '../TableRow/TableRow';
import {useId} from '../../../hooks';
import {TableContext} from '../TableContext';

/**
 * Recursively find the draggable parent not to know which element got dropped on.
 */
const getDropRow = (element: HTMLElement | null): number => {
  if (null === element) throw new Error('Draggable parent not found');

  return undefined !== element.dataset.draggableIndex
    ? parseInt(element.dataset.draggableIndex)
    : getDropRow(element.parentElement);
};

const generateReorderedIndices = (size: number, draggedIndex: number, droppedIndex: number) => {
  //Generate en array of indices from original size
  const originalArray = Array.from([...Array.from({length: size})].keys());

  //Remove the moved element
  const arrayWithoutDraggedItem = originalArray.filter(index => draggedIndex !== index);

  //Place it at the dropped position
  arrayWithoutDraggedItem.splice(droppedIndex, 0, draggedIndex);

  return arrayWithoutDraggedItem;
};

type TableBodyProps = {
  /**
   * Header rows
   */
  children?: ReactNode;
};

const TableBody = React.forwardRef<HTMLTableSectionElement, TableBodyProps>(
  ({children, ...rest}: TableBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const {isOrderable, onReorder} = useContext(TableContext);
    const [draggedElementIndex, setDraggedElementIndex] = useState<number | null>(null);
    const uuid = useId('draggable_');

    const decoratedChildren = isOrderable
      ? Children.map(children, (child, index) => {
          if (!React.isValidElement<TableRowProps>(child)) {
            throw Error('Children of Table.Body should be a valid react element');
          }

          return cloneElement(child, {
            'data-draggable-index': index,
            canBeDraggedOver: null !== draggedElementIndex,
            onDragStart: () => {
              setDraggedElementIndex(index);
            },
          });
        })
      : children;

    return (
      <tbody
        ref={forwardedRef}
        {...rest}
        data-uuid={uuid}
        onDrop={(event: SyntheticEvent<HTMLTableSectionElement>) => {
          if (event.currentTarget.dataset.uuid === uuid && null !== draggedElementIndex && isOrderable && onReorder) {
            const droppedElementIndex = getDropRow(event.target as HTMLElement);

            const newIndices = generateReorderedIndices(
              Children.count(children),
              draggedElementIndex,
              droppedElementIndex
            );
            onReorder(newIndices);

            setDraggedElementIndex(null);
            event.stopPropagation();
          }
        }}
        onDragOver={event => {
          //Needed to trigger the onDrop event.
          event.stopPropagation();
          event.preventDefault();
        }}
      >
        {decoratedChildren}
      </tbody>
    );
  }
);

export {TableBody};
export type {TableBodyProps};
