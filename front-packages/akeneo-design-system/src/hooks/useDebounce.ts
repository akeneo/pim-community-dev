import {useEffect, useState} from 'react';

const useDebounce = <Type = string>(value: Type, delay = 300): Type => {
  const [debouncedValue, setDebouncedValue] = useState<Type>(value);

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

export {useDebounce};
