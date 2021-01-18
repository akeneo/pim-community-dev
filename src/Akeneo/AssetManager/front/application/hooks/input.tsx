import * as React from 'react';

export const useFocus = (): [React.RefObject<HTMLInputElement>, () => void] => {
  const ref = React.useRef<null | HTMLInputElement>(null);
  const setFocus = React.useCallback(() => {
    if (ref.current !== null) ref.current.focus();
  }, [ref]);

  React.useEffect(() => {
    setFocus();
  }, []);

  return [ref, setFocus];
};
