import {useCallback, useEffect, useState} from 'react';

type PlaceholderPosition = 'top' | 'bottom' | 'none';

const usePlaceholderPosition = (rowIndex: number) => {
  const [overingCount, setOveringCount] = useState<number>(0);
  const [placeholderPosition, setPlaceholderPosition] = useState<PlaceholderPosition>('none');

  useEffect(() => {
    setOveringCount(0);
  }, [rowIndex]);

  const dragEnter = useCallback(
    (draggedElementIndex: number) => {
      setOveringCount(count => count + 1);
      setPlaceholderPosition(draggedElementIndex >= rowIndex ? 'top' : 'bottom');
    },
    [rowIndex]
  );

  const dragLeave = useCallback(() => {
    setOveringCount(count => count - 1);
  }, []);

  const dragEnd = useCallback(() => {
    setOveringCount(0);
  }, []);

  return [overingCount === 0 ? 'none' : placeholderPosition, dragEnter, dragLeave, dragEnd] as const;
};

export {usePlaceholderPosition};
export type {PlaceholderPosition};
