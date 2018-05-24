import {startLoading, stopLoading, goNextPage, goFirstPage} from 'akeneoenrichedentity/application/event/search';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Fetcher, {Query} from 'akeneoenrichedentity/application/fetcher/fetcher';
import {dataReceived} from 'akeneoenrichedentity/domain/event/search';
import EnrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';

const stateToQuery = async (state: State): Promise<Query> => {
  return {
    locale: undefined === state.user.uiLocale ? '' : state.user.uiLocale,
    limit: state.grid.query.limit,
    page: state.grid.query.page,
    filters: [],
  };
};

const fetchResults = (fetcher: Fetcher<EnrichedEntity>) => async (
  query: Query
): Promise<{enrichedEntities: EnrichedEntity[]; total: number}> => {
  const {items, total} = await fetcher.search(query);

  return {enrichedEntities: items, total};
};

const updateResultsWithFetcher = (fetcher: Fetcher<EnrichedEntity>) =>
  ((requestCount: number = 0) => {
    return (append: boolean = false) => async (dispatch: any, getState: any): Promise<void> => {
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
        dispatch(dataReceived<EnrichedEntity>(enrichedEntities, total, append));
        dispatch(stopLoading());
      }
    };
  })();

export const updateResults = updateResultsWithFetcher(EnrichedEntityFetcher);
