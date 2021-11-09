import * as React from 'react';

export const useScroll = function<T extends HTMLElement>(): [React.RefObject<T>, () => void] {
  const ref = React.useRef<null | T>(null);
  const scrollTop = React.useCallback(() => {
    if (ref.current !== null) ref.current.scrollTop = 0;
  }, [ref]);

  React.useEffect(scrollTop, []);

  return [ref, scrollTop];
};

export const useKeepVisibleX = function<T extends HTMLElement>() {
  const containerRef = React.useRef<T>(null);
  const elementRef = React.useCallback(
    element => {
      if (null !== containerRef.current && null !== element) {
        const style = getComputedStyle(element);
        const containerRect = containerRef.current.getBoundingClientRect();
        const elementRect = element.getBoundingClientRect();
        const elementWidth = elementRect.width + parseInt(style.marginLeft) + parseInt(style.marginRight);
        if (elementRect.left >= containerRect.right - elementWidth) {
          containerRef.current.scrollLeft += elementRect.left - containerRect.right + elementWidth;
        } else if (elementRect.left <= containerRect.left) {
          containerRef.current.scrollLeft -= containerRect.left - elementRect.left;
        }
      }
    },
    [containerRef.current]
  );

  return {containerRef, elementRef};
};
