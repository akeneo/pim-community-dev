import {useCallback, useEffect, useRef, useState} from 'react';
import {useBooleanState, useIsMounted} from 'akeneo-design-system';
import {useUserContext} from '@akeneo-pim-community/shared';
import {useAvailableTargetsFetcher} from './useAvailableTargetsFetcher';
import {AvailableTargetGroup, defaultTargetOffset, TargetOffset} from '../models';

const useOffsetAvailableTargets = (debouncedSearchValue: string, shouldFetch = true) => {
  const [results, setResults] = useState<AvailableTargetGroup[] | null>(null);
  const [offset, setOffset] = useState<TargetOffset>(defaultTargetOffset);
  const [isLastPage, onLastPage, notOnLastPage] = useBooleanState();
  const isFetching = useRef<boolean>(false);
  const isMounted = useIsMounted();
  const userContext = useUserContext();
  const targetsFetcher = useAvailableTargetsFetcher(debouncedSearchValue, userContext.get('catalogLocale'));

  const fetchResults = useCallback(
    async (offset: TargetOffset) => {
      isFetching.current = true;

      const newResults = await targetsFetcher(offset);

      if (!isMounted()) return;

      if (newResults.results.length === 0) onLastPage();

      setResults(currentResults => {
        if (offset === defaultTargetOffset || null === currentResults) return newResults.results;

        return [...currentResults, ...newResults.results];
      });
      setOffset(newResults.offset);
      isFetching.current = false;
    },
    [isMounted, onLastPage, targetsFetcher]
  );

  useEffect(() => {
    if (!shouldFetch || isFetching.current) return;

    notOnLastPage();
    void fetchResults(defaultTargetOffset);
  }, [debouncedSearchValue, shouldFetch, fetchResults, notOnLastPage]);

  const handleNextPage = () => {
    if (isLastPage || isFetching.current) return;

    void fetchResults(offset);
  };

  return [results ?? [], handleNextPage] as const;
};

export {useOffsetAvailableTargets};
