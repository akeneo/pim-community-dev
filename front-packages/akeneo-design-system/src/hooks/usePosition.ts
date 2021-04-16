import {RefObject, useState, useEffect} from 'react';

type VerticalPosition = 'up' | 'down';
type HorizontalPosition = 'left' | 'right';

/**
 * This hook provides the vertical position that an overlay should have. It's a pretty naive one:
 * It takes the biggest distance to the top or the bottom.
 */
const useVerticalPosition = (ref: RefObject<HTMLElement>, forcedPosition?: VerticalPosition) => {
  const [verticalPosition, setVerticalPosition] = useState<VerticalPosition | undefined>(forcedPosition);

  useEffect(() => {
    if (null !== ref.current && undefined === forcedPosition) {
      const {height: elementHeight, top: distanceToTop} = ref.current.getBoundingClientRect();

      const windowHeight = window.innerHeight || document.documentElement.clientHeight;
      const distanceToBottom = windowHeight - (elementHeight + distanceToTop);

      const elementIsOverlappingBottom = distanceToBottom < 0;
      const elementIsOverlappingTop = distanceToTop < 0;

      setVerticalPosition(elementIsOverlappingBottom ? (elementIsOverlappingTop ? 'down' : 'up') : 'down');
    }
  }, [forcedPosition]);

  return verticalPosition;
};

/**
 * This hook provides the horizontal position that an overlay should have. It's a pretty naive one:
 * It takes the biggest distance to the left or the right.
 */
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
