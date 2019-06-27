import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import updateResultsWithFetcher from 'akeneoreferenceentity/application/action/search';
import {updateFilter, removeFilter, gridStateUpdated} from 'akeneoreferenceentity/application/event/search';
import {CompletenessValue} from 'akeneoreferenceentity/application/component/record/index/completeness-filter';
import {Filter} from 'akeneoreferenceentity/application/reducer/grid';

const stateToQuery = async (state: EditState): Promise<Query> => {
  return {
    locale: undefined === state.user.catalogLocale ? '' : state.user.catalogLocale,
    channel: undefined === state.user.catalogChannel ? '' : state.user.catalogChannel,
    size: state.grid.query.size,
    page: state.grid.query.page,
    filters: [
      ...state.grid.query.filters,
      {
        field: 'reference_entity',
        operator: '=',
        value: state.form.data.identifier,
        context: {},
      },
    ],
  };
};

export const MAX_DISPLAYED_RECORDS = 500;

export const needMoreResults = () => (dispatch: any, getState: any) => {
  if (
    !getState().grid.isFetching &&
    getState().grid.items.length < MAX_DISPLAYED_RECORDS &&
    getState().grid.items.length < getState().grid.matchesCount
  ) {
    dispatch(updateRecordResults(true));
  }
};

export const searchUpdated = (searchInput: string) => (dispatch: any) => {
  dispatch(updateFilter('full_text', '=', searchInput));
  dispatch(updateRecordResults(false));
  dispatch(gridStateUpdated());
};

export const filterUpdated = (updatedFilter: Filter) => (dispatch: any) => {
  dispatch(updateFilter(updatedFilter.field, updatedFilter.operator, updatedFilter.value));
  dispatch(updateRecordResults(false));
  dispatch(gridStateUpdated());
};

export const restoreFilters = (filters: Filter[]) => (dispatch: any) => {
  filters.forEach((filter: Filter) => dispatch(updateFilter(filter.field, filter.operator, filter.value)));
};

export const completenessFilterUpdated = (completenessValue: CompletenessValue) => (dispatch: any) => {
  switch (completenessValue) {
    case CompletenessValue.All:
      dispatch(removeFilter('complete'));
      break;
    case CompletenessValue.Yes:
      dispatch(updateFilter('complete', '=', true));
      break;
    case CompletenessValue.No:
      dispatch(updateFilter('complete', '=', false));
      break;
  }

  dispatch(updateRecordResults(false));
  dispatch(gridStateUpdated());
};

export const updateRecordResults = updateResultsWithFetcher<NormalizedItemRecord>(recordFetcher, stateToQuery);
