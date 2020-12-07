import {useCallback, useEffect, useState} from 'react';
import {debounce} from 'lodash';

const useDebounce = (value: any, delay: number) => {
  const [debouncedValue, setDebouncedValue] = useState(value);

  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(timer);
    };
  }, [value, delay]);

  return debouncedValue;
};

const useDebounceCallback = (callback: (args: any) => any, delay?: number) => {
  return useCallback(debounce(callback, delay), [callback, delay]);
};

export {useDebounce, useDebounceCallback};
