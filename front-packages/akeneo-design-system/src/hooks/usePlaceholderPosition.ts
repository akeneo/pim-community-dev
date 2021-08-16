import {useState} from 'react';

type PlaceholderPosition = 'top' | 'bottom' | 'none';

const usePlaceholderPosition = (rowIndex: number, draggedElement: number | null) => {
  const [overingCount, setOveringCount] = useState(0);
  const [placeholderPosition, setPlaceholderPosition] = useState<PlaceholderPosition>('none');

  return [
    overingCount === 0 ? 'none' : placeholderPosition,
    () => {
      if (null === draggedElement) return;
      setOveringCount(count => count + 1);
      setPlaceholderPosition(draggedElement >= rowIndex ? 'top' : 'bottom');
    },
    () => {
      if (null === draggedElement) return;
      setOveringCount(count => count - 1);
    },
    () => {
      setOveringCount(0);
    },
  ] as const;
};

export {usePlaceholderPosition};
export type {PlaceholderPosition};
