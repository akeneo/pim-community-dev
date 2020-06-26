import {RefObject, useCallback, useEffect} from 'react';

const useAutoFocus = (ref: RefObject<HTMLInputElement>): (() => void) => {
  const focus = useCallback(() => {
    if (ref.current !== null) ref.current.focus();
  }, [ref]);

  useEffect(focus, []);

  return focus;
};

export {useAutoFocus};
