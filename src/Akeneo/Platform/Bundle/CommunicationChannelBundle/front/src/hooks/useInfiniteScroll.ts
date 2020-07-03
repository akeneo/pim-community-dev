import {useCallback, useEffect, useReducer} from 'react';
import {reducer, initialState} from '../reducers/infiniteScrollReducer';
import {
  infiniteScrollFetchingResults,
  infiniteScrollFirstResultsFetched,
  infiniteScrollNextResultsFetched,
  infiniteScrollResultsNotFetched,
} from '../actions/infiniteScrollActions';

/**
 * Hook to do infinite scroll on a list of items fetched
 *
 * @param fetch Function to fetch the paginated items. Those function needs to take in parameter the seard after id and the items limit. It returns a Promise of items.
 * @param scrollableElement HTML element where the scroll is done
 * @param limit Limit numbers of items by qurey to fetch items (By default 10).
 * @param threshold Thereshold Maximum distance to bottom of the scroll to start the fetch of the items (By default 300).
 *
 * @returns {items: any[], isFetching: boolean, hasError: boolean}
 */
const useInfiniteScroll = (
  fetch: (searchAfter: string | null, limit: number) => Promise<any[]>,
  scrollableElement: HTMLElement | null,
  limit: number = 10,
  threshold: number = 300
): {
  items: any[];
  isFetching: boolean;
  hasError: boolean;
} => {
  const [state, dispatch] = useReducer(reducer, initialState);

  const updateState = useCallback(async (searchAfter: string | null, limit: number) => {
    try {
      dispatch(infiniteScrollFetchingResults());
      const data = await fetch(searchAfter, limit);

      if (null === searchAfter) {
        dispatch(infiniteScrollFirstResultsFetched(data));
      } else {
        const lastAppend = data.length < limit;
        dispatch(infiniteScrollNextResultsFetched(data, lastAppend));
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

  useEffect(() => {
    if (null !== scrollableElement) {
      scrollableElement.onscroll = () => {
        if (hasToAppendItems(scrollableElement, state.lastAppend, state.isFetching)) {
          const lastElement = state.items[state.items.length - 1];
          const searchAfter = lastElement.id;
          updateState(searchAfter, limit);
        }
      };
    } else {
      updateState(null, limit);
    }
  }, [scrollableElement, state]);

  return {items: state.items, isFetching: state.isFetching, hasError: state.hasError};
};

export {useInfiniteScroll};
