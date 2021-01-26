import {useCallback, useEffect, useState} from 'react';
import {debounceCallback} from '../tools';

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

/**
 * Grouping multiple calls to a function (like AJAX request) into a single one
 * @param callback
 * @param delay
 */
const useDebounceCallback = (callback: (...args: any) => any, delay: number) => {
  return useCallback(debounceCallback(callback, delay), [callback, delay]);
};

export {useDebounce, useDebounceCallback};
