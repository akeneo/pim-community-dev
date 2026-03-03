import {useBooleanState, useIsMounted} from '.';
import {DependencyList, useEffect, useState} from 'react';

const usePaginatedResults = <Type>(
  fetcher: (page: number) => Promise<Type[]>,
  dependencies: DependencyList,
  shouldFetch = true
) => {
  const [results, setResults] = useState<Type[] | null>(null);
  const [page, setPage] = useState<number>(0);
  const [isFetching, startFetching, stopFetching] = useBooleanState();
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();
  const isMounted = useIsMounted();

  useEffect(() => {
    if (null === results) return;

    setPage(0);
    notOnLastPage();
    // We need this to re-trigger the fetching of results in case of search on first load
    setResults([...results]);
  }, dependencies);

  useEffect(() => {
    if (isFetching || isLastPage || !shouldFetch) return;

    const fetchResults = async () => {
      const newResults = await fetcher(page);

      if (!isMounted()) return;

      if (newResults.length === 0) onLastPage();

      setResults(currentResults => {
        if (0 === page || null === currentResults) return newResults;

        return [...currentResults, ...newResults];
      });
      stopFetching();
    };

    startFetching();
    void fetchResults();
  }, [page, results, shouldFetch]);

  useEffect(() => {
    if (shouldFetch) return;

    setPage(0);
    setResults(null);
    notOnLastPage();
  }, [shouldFetch]);

  const fetchNextPage = () => {
    if (isFetching || isLastPage) return;

    setPage(page => page + 1);
  };

  return [results ?? [], fetchNextPage] as const;
};

export {usePaginatedResults};
