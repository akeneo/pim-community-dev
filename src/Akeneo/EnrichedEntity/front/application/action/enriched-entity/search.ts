import {IndexState} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import EnrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import updateResultsWithFetcher from 'akeneoenrichedentity/application/action/search';

const stateToQuery = async (state: IndexState): Promise<Query> => {
  return {
    locale: undefined === state.user.uiLocale ? '' : state.user.uiLocale,
    limit: state.grid.query.limit,
    page: state.grid.query.page,
    filters: [],
  };
};

export const updateEnrichedEntityResults = updateResultsWithFetcher<EnrichedEntity>(
  EnrichedEntityFetcher,
  stateToQuery
);
