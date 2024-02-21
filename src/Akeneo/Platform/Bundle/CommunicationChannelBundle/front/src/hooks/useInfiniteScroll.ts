import {useCallback, useEffect, useReducer} from 'react';
import {reducer, initialState} from '../reducers/infiniteScrollReducer';
import {
  infiniteScrollFetchingResults,
  infiniteScrollFirstResultsFetched,
  infiniteScrollNextResultsFetched,
  infiniteScrollResultsNotFetched,
} from '../actions/infiniteScrollActions';
import {useIsMounted} from '@akeneo-pim-community/shared';

type ResultsResponse = {
  items: any[];
  isFetching: boolean;
  hasError: boolean;
};

/**
 * Hook to do infinite scroll on a list of items fetched
 *
 * @param fetch Function to fetch the paginated items. Those function needs to take in parameter the seard after id. It returns a Promise of items.
 * @param scrollableElement HTML element where the scroll is done
 * @param threshold Thereshold Maximum distance to bottom of the scroll to start the fetch of the items (By default 300).
 *
 * @returns [ResultsResponse, callbackToHandleFetchingResults]
 */
const useInfiniteScroll = (
  fetch: (searchAfter: string | null) => Promise<any[]>,
  scrollableElement: HTMLElement | null,
  threshold: number = 4000
): [ResultsResponse, (searchAfter: string | null) => void] => {
  const [state, dispatch] = useReducer(reducer, initialState);
  const isMounted = useIsMounted();

  const handleFetchingResults = useCallback(async (searchAfter: string | null) => {
    try {
      dispatch(infiniteScrollFetchingResults());
      const data = await fetch(searchAfter);

      if (isMounted()) {
        if (null === searchAfter) {
          dispatch(infiniteScrollFirstResultsFetched(data));
        } else {
          const lastAppend = data.length == 0;
          dispatch(infiniteScrollNextResultsFetched(data, lastAppend));
        }
      }
    } catch (error) {
      dispatch(infiniteScrollResultsNotFetched());
    }
  }, []);

  const hasToAppendItems = (scrollableElement: HTMLElement, lastAppend: boolean, isFetching: boolean) => {
    const scrollPosition = scrollableElement.scrollTop;
    const clientHeight = scrollableElement.clientHeight;
    const scrollSize = scrollableElement.scrollHeight;

    return scrollPosition + clientHeight >= scrollSize - threshold && !lastAppend && !isFetching;
  };

  if (null !== scrollableElement) {
    scrollableElement.onscroll = () => {
      if (hasToAppendItems(scrollableElement, state.lastAppend, state.isFetching)) {
        const lastElement = state.items[state.items.length - 1];
        const searchAfter = lastElement.id;
        handleFetchingResults(searchAfter);
      }
    };
  }

  useEffect(() => {
    handleFetchingResults(null);
  }, []);

  return [{items: state.items, isFetching: state.isFetching, hasError: state.hasError}, handleFetchingResults];
};

export {useInfiniteScroll};
