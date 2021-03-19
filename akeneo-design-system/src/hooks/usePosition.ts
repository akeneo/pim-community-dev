import {RefObject, useState, useEffect} from 'react';

type VerticalPosition = 'up' | 'down';
const useVerticalPosition = (ref: RefObject<HTMLElement>, defaultPosition: VerticalPosition = 'down') => {
  const [verticalPosition, setVerticalPosition] = useState<VerticalPosition>(defaultPosition);

  useEffect(() => {
    if (null !== ref.current && undefined === defaultPosition) {
      const {height: elementHeight, top: distanceToTop} = ref.current.getBoundingClientRect();
      const windowHeight = window.innerHeight;
      const distanceToBottom = windowHeight - (elementHeight + distanceToTop);

      setVerticalPosition(distanceToTop > distanceToBottom ? 'up' : 'down');
    }
  }, [defaultPosition]);

  return verticalPosition
}

type HorizontalPosition = 'left' | 'right';
const useHorizontalPosition = (ref: RefObject<HTMLElement>, defaultPosition: HorizontalPosition = 'right') => {
  const [horizontalPosition, setHorizontalPosition] = useState<HorizontalPosition>(defaultPosition);

  useEffect(() => {
    if (null !== ref.current && undefined === defaultPosition) {
      const {width: elementWidth, left: distanceToLeft} = ref.current.getBoundingClientRect();
      const windowWidth = window.innerWidth;
      const distanceToRight = windowWidth - (elementWidth + distanceToLeft);

      setHorizontalPosition(distanceToLeft > distanceToRight ? 'left' : 'right');
    }
  }, [defaultPosition]);

  return horizontalPosition
}

export {useVerticalPosition, useHorizontalPosition};
export type {VerticalPosition, HorizontalPosition};
