import {useCallback, useState} from 'react';

const useDragElementIndex = () => {
  const [draggedElementIndex, setDraggedElementIndex] = useState<number | null>(null);
  const onDragStart = useCallback((index: number) => setDraggedElementIndex(index), []);
  const onDragEnd = useCallback(() => setDraggedElementIndex(null), []);

  return [draggedElementIndex, onDragStart, onDragEnd] as const;
};

export {useDragElementIndex};
