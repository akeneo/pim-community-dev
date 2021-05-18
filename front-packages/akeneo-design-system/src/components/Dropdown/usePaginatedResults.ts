import {useBooleanState} from 'hooks';
import {useEffect, useState} from 'react';

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

const usePaginatedResults = <Type>(fetcher: (page: number) => Promise<Type[]>) => {
  const [results, setResults] = useState<Type[] | null>(null);
  const [page, setPage] = useState<number>(0);
  const [isFetching, startFetching, stopFetching] = useBooleanState();
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();

  useEffect(() => {
    if (null !== results) {
      setResults(null);
      setPage(0);
      notOnLastPage();
    }
  }, [fetcher]);

  useEffect(() => {
    if (isFetching || isLastPage) return;

    startFetching();
    fetcher(page).then(newResults => {
      if (newResults.length === 0) onLastPage();
      setResults(currentResults => [...(currentResults ?? []), ...newResults]);
      stopFetching();
    });
  }, [page]);

  return [results ?? [], () => setPage(page => (isFetching || isLastPage ? page : page + 1))];
};

export {usePaginatedResults, useDebounce};
