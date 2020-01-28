import * as React from 'react';

export const useScroll = function<T extends HTMLElement>(): [React.RefObject<T>, () => void] {
  const ref = React.useRef<null | T>(null);
  const scrollTop = React.useCallback(() => {
    if (ref.current !== null) ref.current.scrollTop = 0;
  }, [ref]);

  React.useEffect(scrollTop, []);

  return [ref, scrollTop];
};
