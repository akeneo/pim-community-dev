import {useCallback, useEffect} from 'react';
import {Key} from '../tools';

const useShortcut = (key: Key, callback: Function) => {
  const memoizedCallback = useCallback((event: KeyboardEvent) => (key === event.key ? callback(event) : null), [
    key,
    callback,
  ]);

  useEffect(() => {
    document.addEventListener('keydown', memoizedCallback);
    return () => document.removeEventListener('keydown', memoizedCallback);
  }, [memoizedCallback]);
};

export {useShortcut};
