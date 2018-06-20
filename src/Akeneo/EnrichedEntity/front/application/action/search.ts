import {startLoading, stopLoading, goNextPage, goFirstPage} from 'akeneoenrichedentity/application/event/search';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import Fetcher, {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import {dataReceived} from 'akeneoenrichedentity/domain/event/search';

const fetchResults = <Object>(fetcher: Fetcher<Object>) => async (
  query: Query
): Promise<{enrichedEntities: Object[]; total: number}> => {
  const {items, total} = await fetcher.search(query);

  return {enrichedEntities: items, total};
};

export const updateResultsWithFetcher = <Object>(
  fetcher: Fetcher<Object>,
  stateToQuery: (state: any) => Promise<Query>
) =>
  ((requestCount: number = 0) => {
    return (append: boolean = false): any => async (dispatch: any, getState: any): Promise<void> => {
      requestCount++;
      const state = getState() as State;
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
      const {enrichedEntities, total} = await fetchResults(fetcher)(query);

      if (requestCount === currentRequestCount) {
        dispatch(dataReceived<Object>(enrichedEntities, total, append));
        dispatch(stopLoading());
      }
    };
  })();
