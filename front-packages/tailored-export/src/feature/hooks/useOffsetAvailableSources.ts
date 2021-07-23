import {useCallback, useEffect, useRef, useState} from 'react';
import {useBooleanState, useIsMounted} from 'akeneo-design-system';
import {useUserContext} from '@akeneo-pim-community/shared';
import {useAvailableSourcesFetcher} from './useAvailableSourcesFetcher';
import {AvailableSourceGroup, defaultSourceOffset, SourceOffset} from '../models/AvailableSource';

const useOffsetAvailableSources = (debouncedSearchValue: string, shouldFetch = true) => {
  const [results, setResults] = useState<AvailableSourceGroup[] | null>(null);
  const [offset, setOffset] = useState<SourceOffset>(defaultSourceOffset);
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();
  const isFetching = useRef<boolean>(false);
  const isMounted = useIsMounted();
  const userContext = useUserContext();
  const sourcesFetcher = useAvailableSourcesFetcher(debouncedSearchValue, userContext.get('catalogLocale'));

  const fetchResults = useCallback(
    async (offset: SourceOffset) => {
      isFetching.current = true;

      const newResults = await sourcesFetcher(offset);

      if (!isMounted()) return;

      if (newResults.results.length === 0) onLastPage();

      setResults(currentResults => {
        if (offset === defaultSourceOffset || null === currentResults) return newResults.results;

        return [...currentResults, ...newResults.results];
      });
      setOffset(newResults.offset);
      isFetching.current = false;
    },
    [isMounted, onLastPage, sourcesFetcher]
  );

  useEffect(() => {
    if (!shouldFetch || isFetching.current) return;

    notOnLastPage();
    fetchResults(defaultSourceOffset);
  }, [debouncedSearchValue, shouldFetch, fetchResults, notOnLastPage]);

  const handleNextPage = () => {
    if (isLastPage || isFetching.current) return;

    void fetchResults(offset);
  };

  return [results ?? [], handleNextPage] as const;
};

export {useOffsetAvailableSources};
