import {RefObject, useCallback, useEffect} from 'react';

const useAutoFocus = (ref: RefObject<HTMLElement>): (() => void) => {
  const focus = useCallback(() => {
    setTimeout(() => {
      if (ref.current !== null) ref.current.focus();
    }, 0);
  }, [ref]);

  useEffect(focus, []);

  return focus;
};

export {useAutoFocus};
