import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import recordFetcher from 'akeneoenrichedentity/infrastructure/fetcher/record';
import updateResultsWithFetcher from 'akeneoenrichedentity/application/action/search';

const stateToQuery = async (state: EditState): Promise<Query> => {
  return {
    locale: undefined === state.user.uiLocale ? '' : state.user.uiLocale,
    limit: state.grid.query.limit,
    page: state.grid.query.page,
    filters: [
      {
        field: 'enriched_entity',
        operator: '=',
        value: state.form.data.identifier,
        context: {},
      },
    ],
  };
};

export const updateRecordResults = updateResultsWithFetcher<Record>(recordFetcher, stateToQuery);
