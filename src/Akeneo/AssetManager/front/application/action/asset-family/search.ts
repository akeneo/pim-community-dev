import {IndexState} from 'akeneoreferenceentity/application/reducer/reference-entity/index';
import ReferenceEntityListItem from 'akeneoreferenceentity/domain/model/reference-entity/list';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import ReferenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import updateResultsWithFetcher from 'akeneoreferenceentity/application/action/search';

const stateToQuery = async (state: IndexState): Promise<Query> => {
  return {
    locale: undefined === state.user.catalogLocale ? '' : state.user.catalogLocale,
    channel: undefined === state.user.catalogChannel ? '' : state.user.catalogChannel,
    size: state.grid.query.size,
    page: state.grid.query.page,
    filters: [],
  };
};

export const updateReferenceEntityResults = updateResultsWithFetcher<ReferenceEntityListItem>(
  ReferenceEntityFetcher,
  stateToQuery
);
