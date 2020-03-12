import React, {useCallback} from 'react';
import {Key} from 'akeneomeasure/shared/key';

const useShortcut = (key: Key, callback: () => void) => {
  const memoizedCallback = useCallback((event: KeyboardEvent) => (key === event.code ? callback() : null), [
    key,
    callback,
  ]);

  React.useEffect(() => {
    document.addEventListener('keydown', memoizedCallback);
    return () => document.removeEventListener('keydown', memoizedCallback);
  }, [memoizedCallback]);
};

export {useShortcut};
