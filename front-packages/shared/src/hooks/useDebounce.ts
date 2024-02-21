import {useCallback, useEffect, useState} from 'react';

const debounceCallback = (callback: (...args: any[]) => any, delay: number) => {
  let timer: number;

  return (...args: any[]) => {
    const context = this;

    clearTimeout(timer);
    timer = window.setTimeout(() => {
      callback.apply(context, args);
    }, delay);
  };
};

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
