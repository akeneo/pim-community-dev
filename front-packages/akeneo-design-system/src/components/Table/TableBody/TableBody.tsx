import React, {ReactNode, Ref, SyntheticEvent, useState} from 'react';
import {TableRowProps} from '../TableRow/TableRow';
import {useId} from '../../../hooks';

const getDropRow = (element: HTMLElement | null): number => {
  if (null === element) throw new Error('Parent not found');

  return undefined !== element.dataset.index ? parseInt(element.dataset.index) : getDropRow(element.parentElement);
};

const generateReorderedIndices = (size: number, draggedIndex: number, droppedIndex: number) => {
  const originalArray = Array.from([...new Array(size)].keys());
  const arrayWithoutDraggedItem = originalArray.filter(index => draggedIndex !== index);

  arrayWithoutDraggedItem.splice(droppedIndex, 0, draggedIndex);

  return arrayWithoutDraggedItem;
};

type TableBodyProps = {
  onReorder?: (indices: number[]) => void;

  /**
   * Header rows
   */
  children?: ReactNode;
};

const TableBody = React.forwardRef<HTMLTableSectionElement, TableBodyProps>(
  ({children, onReorder, ...rest}: TableBodyProps, forwardedRef: Ref<HTMLTableSectionElement>) => {
    const [draggedElement, setDraggedElement] = useState<number | null>(null);
    const uuid = useId('draggable_');

    let totalDraggableItems = 0;
    const decoratedChildren =
      undefined !== onReorder
        ? React.Children.map(children, child => {
            if (React.isValidElement<TableRowProps>(child)) {
              const currentDraggableIndex = totalDraggableItems;
              totalDraggableItems++;

              return React.cloneElement(child, {
                draggable: true,
                'data-index': currentDraggableIndex,
                onDragStart: () => {
                  setDraggedElement(currentDraggableIndex);
                },
              });
            }

            return child;
          })
        : children;

    return (
      <tbody
        ref={forwardedRef}
        {...rest}
        data-uuid={uuid}
        onDrop={(event: SyntheticEvent<HTMLTableSectionElement>) => {
          if (event.currentTarget.dataset.uuid === uuid && null !== draggedElement && onReorder) {
            const droppedElement = getDropRow(event.target as HTMLElement);
            console.log(`Dragged element "${draggedElement}" got dropped at position "${droppedElement}"`);
            const newIndices = generateReorderedIndices(totalDraggableItems, draggedElement, droppedElement);
            console.log('new indicies are', newIndices);
            onReorder(newIndices);
            setDraggedElement(null);
            event.stopPropagation();
          }
        }}
        onDragOver={event => {
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
