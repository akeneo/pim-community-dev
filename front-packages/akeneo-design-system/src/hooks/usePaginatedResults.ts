import {useBooleanState, useIsMounted} from '.';
import {DependencyList, useCallback, useEffect, useState} from 'react';

const usePaginatedResults = <Type>(fetcher: (page: number) => Promise<Type[]>, dependencies: DependencyList) => {
  const [results, setResults] = useState<Type[] | null>(null);
  const [page, setPage] = useState<number>(0);
  const [isFetching, startFetching, stopFetching] = useBooleanState();
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();
  const memoisedFetcher = useCallback(fetcher, dependencies);
  const isMounted = useIsMounted();

  useEffect(() => {
    console.log('ho!', page, dependencies);
    if (null === results) return;

    setPage(0);
    notOnLastPage();
    // We need this to re-trigger the fetching of results in case of search on first load
    setResults([...results]);
  }, [memoisedFetcher]);

  useEffect(() => {
    if (isFetching || isLastPage) return;

    const fetchResults = async () => {
      const newResults = await memoisedFetcher(page);

      if (!isMounted()) return;

      if (newResults.length === 0) onLastPage();

      setResults(currentResults => {
        if (0 === page || null === currentResults) return newResults;

        return [...currentResults, ...newResults];
      });
      stopFetching();
    };

    startFetching();
    fetchResults();
  }, [page, results]);

  const fetchNextPage = () => {
    if (isFetching || isLastPage) return;

    setPage(page => page + 1);
  };

  return [results ?? [], fetchNextPage] as const;
};

export {usePaginatedResults};
