import {RefObject, useState, useEffect} from 'react';

type VerticalPosition = 'up' | 'down';
type HorizontalPosition = 'left' | 'right';

const useVerticalPosition = (ref: RefObject<HTMLElement>, forcedPosition?: VerticalPosition) => {
  const [verticalPosition, setVerticalPosition] = useState<VerticalPosition | undefined>(forcedPosition);

  useEffect(() => {
    if (null !== ref.current && undefined === forcedPosition) {
      const {height: elementHeight, top: distanceToTop} = ref.current.getBoundingClientRect();
      const windowHeight = window.innerHeight;
      const distanceToBottom = windowHeight - (elementHeight + distanceToTop);

      setVerticalPosition(distanceToTop > distanceToBottom ? 'up' : 'down');
    }
  }, [forcedPosition]);

  return verticalPosition;
};

const useHorizontalPosition = (ref: RefObject<HTMLElement>, forcedPosition?: HorizontalPosition) => {
  const [horizontalPosition, setHorizontalPosition] = useState<HorizontalPosition | undefined>(forcedPosition);

  useEffect(() => {
    if (null !== ref.current && undefined === forcedPosition) {
      const {width: elementWidth, left: distanceToLeft} = ref.current.getBoundingClientRect();
      const windowWidth = window.innerWidth;
      const distanceToRight = windowWidth - (elementWidth + distanceToLeft);

      setHorizontalPosition(distanceToLeft > distanceToRight ? 'left' : 'right');
    }
  }, [forcedPosition]);

  return horizontalPosition;
};

export {useVerticalPosition, useHorizontalPosition};
export type {VerticalPosition, HorizontalPosition};
