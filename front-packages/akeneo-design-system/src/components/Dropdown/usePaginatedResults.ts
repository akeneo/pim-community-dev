import {useBooleanState} from 'hooks';
import {DependencyList, useCallback, useEffect, useState} from 'react';

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

const usePaginatedResults = <Type>(fetcher: (page: number) => Promise<Type[]>, dependencies: DependencyList[]) => {
  const [results, setResults] = useState<Type[] | null>(null);
  const [page, setPage] = useState<number>(0);
  const [isFetching, startFetching, stopFetching] = useBooleanState();
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();
  const memoisedFetcher = useCallback(fetcher, dependencies);

  useEffect(() => {
    if (null !== results) {
      setPage(0);
      notOnLastPage();
    }
  }, [memoisedFetcher]);

  useEffect(() => {
    if (isFetching || isLastPage) return;

    startFetching();
    memoisedFetcher(page).then(newResults => {
      if (newResults.length === 0) onLastPage();

      setResults(currentResults => [...(0 === page || null === currentResults ? [] : currentResults), ...newResults]);
      stopFetching();
    });
  }, [page, memoisedFetcher, results]);

  const fetchNextPage = () => setPage(page => (isFetching || isLastPage ? page : page + 1));

  return [results ?? [], fetchNextPage];
};

export {usePaginatedResults, useDebounce};
