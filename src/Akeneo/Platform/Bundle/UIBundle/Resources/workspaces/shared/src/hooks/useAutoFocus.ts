import {RefObject, useCallback, useEffect} from 'react';

//TODO this does not work
const useAutoFocus = (ref: RefObject<HTMLInputElement | HTMLTextAreaElement>): (() => void) => {
  const focus = useCallback(() => {
    if (ref.current !== null) ref.current.focus();
  }, [ref]);

  useEffect(focus, []);

  return focus;
};

export {useAutoFocus};
