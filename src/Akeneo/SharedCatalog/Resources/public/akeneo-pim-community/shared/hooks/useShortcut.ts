import {useCallback, useEffect} from 'react';
import {Key} from '../tools/keyboard';

const useShortcut = (key: Key, callback: () => void) => {
  const memoizedCallback = useCallback((event: KeyboardEvent) => (key === event.code ? callback() : null), [
    key,
    callback,
  ]);

  useEffect(() => {
    document.addEventListener('keydown', memoizedCallback);
    return () => document.removeEventListener('keydown', memoizedCallback);
  }, [memoizedCallback]);
};

export {useShortcut};
