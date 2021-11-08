import {startLoading, stopLoading, goNextPage, goFirstPage} from 'akeneoreferenceentity/application/event/search';
import {SearchFetcher, Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import {dataReceived} from 'akeneoreferenceentity/domain/event/search';

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

      const query = await stateToQuery(getState());
      const {items, matchesCount, totalCount} = await fetcher.search(query);

      if (requestCount === currentRequestCount) {
        dispatch(dataReceived<Object>(items, matchesCount, totalCount, append));
        dispatch(stopLoading());
      }
    };
  })();

export default updateResultsWithFetcher;
