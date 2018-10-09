import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import updateResultsWithFetcher from 'akeneoreferenceentity/application/action/search';

const stateToQuery = async (state: EditState): Promise<Query> => {
  return {
    locale: undefined === state.user.uiLocale ? '' : state.user.uiLocale,
    size: state.grid.query.size,
    page: state.grid.query.page,
    filters: [
      {
        field: 'reference_entity',
        operator: '=',
        value: state.form.data.identifier,
        context: {},
      },
    ],
  };
};

export const updateRecordResults = updateResultsWithFetcher<Record>(recordFetcher, stateToQuery);
