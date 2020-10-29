const INFINITE_SCROLL_FETCHING_RESULTS = 'INFINITE_SCROLL_FETCHING_RESULTS';
type InfiniteScrollFetchingResultsAction = {
  type: typeof INFINITE_SCROLL_FETCHING_RESULTS;
};
const infiniteScrollFetchingResults = (): InfiniteScrollFetchingResultsAction => ({
  type: INFINITE_SCROLL_FETCHING_RESULTS,
});

const INFINITE_SCROLL_FIRST_RESULTS_FETCHED = 'INFINITE_SCROLL_FIRST_RESULTS_FETCHED';
type InfiniteScrollFirstResultsFetchedAction = {
  type: typeof INFINITE_SCROLL_FIRST_RESULTS_FETCHED;
  payload: {
    items: any[];
  };
};
const infiniteScrollFirstResultsFetched = (items: any[]): InfiniteScrollFirstResultsFetchedAction => ({
  type: INFINITE_SCROLL_FIRST_RESULTS_FETCHED,
  payload: {
    items,
  },
});

const INFINITE_SCROLL_NEXT_RESULTS_FETCHED = 'INFINITE_SCROLL_NEXT_RESULTS_FETCHED';
type InfiniteScrollNextResultsFetchedAction = {
  type: typeof INFINITE_SCROLL_NEXT_RESULTS_FETCHED;
  payload: {
    items: any[];
    lastAppend: boolean;
  };
};
const infiniteScrollNextResultsFetched = (
  items: any[],
  lastAppend: boolean
): InfiniteScrollNextResultsFetchedAction => ({
  type: INFINITE_SCROLL_NEXT_RESULTS_FETCHED,
  payload: {
    items,
    lastAppend,
  },
});

const INFINITE_SCROLL_RESULTS_NOT_FETCHED = 'INFINITE_SCROLL_RESULTS_NOT_FETCHED';
type InfiniteScrollResultsNotFetchedAction = {
  type: typeof INFINITE_SCROLL_RESULTS_NOT_FETCHED;
};
const infiniteScrollResultsNotFetched = (): InfiniteScrollResultsNotFetchedAction => ({
  type: INFINITE_SCROLL_RESULTS_NOT_FETCHED,
});

type Actions =
  | InfiniteScrollFetchingResultsAction
  | InfiniteScrollFirstResultsFetchedAction
  | InfiniteScrollNextResultsFetchedAction
  | InfiniteScrollResultsNotFetchedAction;

export {
  INFINITE_SCROLL_FETCHING_RESULTS,
  INFINITE_SCROLL_FIRST_RESULTS_FETCHED,
  INFINITE_SCROLL_NEXT_RESULTS_FETCHED,
  INFINITE_SCROLL_RESULTS_NOT_FETCHED,
  infiniteScrollFetchingResults,
  infiniteScrollFirstResultsFetched,
  infiniteScrollNextResultsFetched,
  infiniteScrollResultsNotFetched,
  Actions,
};
