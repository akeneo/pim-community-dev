import {useCallback, useState} from 'react';

type PlaceholderPosition = 'top' | 'bottom' | 'none';

const usePlaceholderPosition = (rowIndex: number, draggedElement: number | null) => {
  const [overingCount, setOveringCount] = useState(0);
  const [placeholderPosition, setPlaceholderPosition] = useState<PlaceholderPosition>('none');

  const dragEnter = useCallback(() => {
    if (null === draggedElement) return;
    setOveringCount(count => count + 1);
    setPlaceholderPosition(draggedElement >= rowIndex ? 'top' : 'bottom');
  }, [draggedElement, rowIndex]);

  const dragLeave = useCallback(() => {
    if (null === draggedElement) return;
    setOveringCount(count => count - 1);
  }, [draggedElement]);

  const dragEnd = useCallback(() => {
    setOveringCount(0);
  }, []);

  return [overingCount === 0 ? 'none' : placeholderPosition, dragEnter, dragLeave, dragEnd] as const;
};

export {usePlaceholderPosition};
export type {PlaceholderPosition};
