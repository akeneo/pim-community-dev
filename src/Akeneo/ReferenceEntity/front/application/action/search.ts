import {startLoading, stopLoading, goNextPage, goFirstPage} from 'akeneoreferenceentity/application/event/search';
import {SearchFetcher, Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import {dataReceived} from 'akeneoreferenceentity/domain/event/search';

const fetchResults = <Object>(fetcher: SearchFetcher<Object>) => async (
  query: Query
): Promise<{items: Object[]; total: number}> => {
  const {items, total} = await fetcher.search(query);

  return {items, total};
};

const updateResultsWithFetcher = <Object>(
  fetcher: SearchFetcher<Object>,
  stateToQuery: (state: any) => Promise<Query>
) =>
  ((requestCount: number = 0) => {
    return (append: boolean = false): any => async (dispatch: any, getState: any): Promise<void> => {
      requestCount++;
      const state = getState();
      const currentRequestCount = requestCount;
      if (append && state.grid.isFetching) {
        return Promise.resolve();
      }

      dispatch(startLoading());

      if (append) {
        dispatch(goNextPage());
      } else {
        dispatch(goFirstPage());
      }

      const query = await stateToQuery(state);
      const {items, total} = await fetchResults<Object>(fetcher)(query);

      if (requestCount === currentRequestCount) {
        dispatch(dataReceived<Object>(items, total, append));
        dispatch(stopLoading());
      }
    };
  })();

export default updateResultsWithFetcher;
