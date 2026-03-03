import {useRef} from 'react';

export const useDebounceCallback = <T extends unknown[]>(callback: (...args: T) => void, delay: number) => {
  const timerRef = useRef<NodeJS.Timeout | null>(null);
  return (...args: T) => {
    if (timerRef.current) {
      clearTimeout(timerRef.current);
    }
    timerRef.current = setTimeout(() => callback(...args), delay);
  };
};
