import {useState} from 'react';

const useDragElementIndex = () => {
  const [draggedElementIndex, setDraggedElementIndex] = useState<number | null>(null);

  return [
    draggedElementIndex,
    (index: number) => setDraggedElementIndex(index),
    () => setDraggedElementIndex(null),
  ] as const;
};

export {useDragElementIndex};
