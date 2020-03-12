import React, {useRef, useCallback, useEffect} from 'react';

const useFocus = (): [React.RefObject<HTMLInputElement>, () => void] => {
  const ref = useRef<null | HTMLInputElement>(null);
  const setFocus = useCallback(() => {
    if (ref.current !== null) ref.current.focus();
  }, [ref]);

  useEffect(() => {
    setFocus();
  }, []);

  return [ref, setFocus];
};

export {useFocus};
