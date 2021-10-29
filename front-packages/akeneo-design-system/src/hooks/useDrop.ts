import {useId} from './index';
import {DragEvent} from 'react';

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
  //Generate an array of indices from original size
  const originalArray = Array.from([...Array.from({length: size})].keys());

  //Remove the moved element
  const arrayWithoutDraggedItem = originalArray.filter(index => draggedIndex !== index);

  //Place it at the dropped position
  arrayWithoutDraggedItem.splice(droppedIndex, 0, draggedIndex);

  return arrayWithoutDraggedItem;
};

const useDrop = (
  tableSize: number,
  draggedElementIndex: number | null,
  onReorder: ((reorderedIndices: number[]) => void) | undefined
) => {
  const tableId = useId('table_');

  const handleDrop = (event: DragEvent<HTMLTableSectionElement>) => {
    event.stopPropagation();
    event.preventDefault();

    if (event.currentTarget.dataset.tableId === tableId && onReorder && null !== draggedElementIndex) {
      const droppedElementIndex = getDropRow(event.target as HTMLElement);
      const newIndices = generateReorderedIndices(tableSize, draggedElementIndex, droppedElementIndex);

      onReorder(newIndices);
    }
  };

  const handleDragOver = (event: DragEvent<HTMLTableSectionElement>) => {
    //Needed to trigger the onDrop event.
    event.stopPropagation();
    event.preventDefault();
  };

  return [tableId, handleDrop, handleDragOver] as const;
};

export {useDrop};
