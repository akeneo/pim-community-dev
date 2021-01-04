import * as React from 'react';
import Key from 'akeneoassetmanager/tools/key';

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

export const useShortcut = (key: Key, callback: () => void) => {
  const memoizedCallback = React.useCallback((event: KeyboardEvent) => (key === event.key ? callback() : null), [
    key,
    callback,
  ]);

  React.useEffect(() => {
    document.addEventListener('keydown', memoizedCallback);
    return () => document.removeEventListener('keydown', memoizedCallback);
  }, [memoizedCallback]);
};
