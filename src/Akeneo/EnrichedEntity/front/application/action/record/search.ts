import {startLoading, stopLoading, goNextPage, goFirstPage} from 'akeneoenrichedentity/application/event/search';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import {dataReceived} from 'akeneoenrichedentity/domain/event/search';
import recordFetcher from 'akeneoenrichedentity/infrastructure/fetcher/record';
import RecordFetcher from 'akeneoenrichedentity/domain/fetcher/record';

const stateToQuery = async (state: State): Promise<Query> => {
  return {
    locale: undefined === state.user.uiLocale ? '' : state.user.uiLocale,
    limit: state.grid.query.limit,
    page: state.grid.query.page,
    filters: [],
  };
};

const fetchResults = (fetcher: RecordFetcher) => async (
  query: Query
): Promise<{enrichedEntities: Record[]; total: number}> => {
  const {items, total} = await fetcher.search(query);

  return {enrichedEntities: items, total};
};

const updateResultsWithFetcher = (fetcher: RecordFetcher) =>
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
        dispatch(dataReceived<Record>(enrichedEntities, total, append));
        dispatch(stopLoading());
      }
    };
  })();

export const updateResults = updateResultsWithFetcher(recordFetcher);
