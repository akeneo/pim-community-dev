import {useCallback, useState} from 'react';

const useDragElementIndex = () => {
  const [draggedElementIndex, setDraggedElementIndex] = useState<number | null>(null);
  const onDragStart = useCallback((index: number) => setDraggedElementIndex(index), [setDraggedElementIndex]);
  const onDragEnd = useCallback(() => setDraggedElementIndex(null), [setDraggedElementIndex]);

  return [draggedElementIndex, onDragStart, onDragEnd] as const;
};

export {useDragElementIndex};
