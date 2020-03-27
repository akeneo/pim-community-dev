import {useRef, useCallback, useEffect, RefObject} from 'react';

const useFocus = (): [RefObject<HTMLInputElement>, () => void] => {
  const ref = useRef<HTMLInputElement | null>(null);
  const setFocus = useCallback(() => {
    if (ref.current !== null) ref.current.focus();
  }, [ref]);

  useEffect(setFocus, []);

  return [ref, setFocus];
};

export {useFocus};
