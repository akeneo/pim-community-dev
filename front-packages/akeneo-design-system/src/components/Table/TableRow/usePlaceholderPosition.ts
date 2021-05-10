import {DragEvent, useState} from 'react';

type PlaceholderPosition = 'top' | 'bottom' | 'none';

const usePlaceholderPosition = (rowIndex: number) => {
  const [overingCount, setOveringCount] = useState(0);
  const [placeholderPosition, setPlaceholderPosition] = useState<PlaceholderPosition>('none');

  return [
    overingCount === 0 ? 'none' : placeholderPosition,
    (event: DragEvent) => {
      setOveringCount(count => count + 1);
      const draggedElementIndex = Number(event.dataTransfer.getData('text/plain'));
      setPlaceholderPosition(
        draggedElementIndex === rowIndex ? 'none' : draggedElementIndex > rowIndex ? 'top' : 'bottom'
      );
    },
    () => {
      setOveringCount(count => count - 1);
    },
    () => {
      setOveringCount(0);
    },
  ] as const;
};

export {usePlaceholderPosition};
export type {PlaceholderPosition};
