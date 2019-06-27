import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import updateResultsWithFetcher from 'akeneoassetmanager/application/action/search';
import {updateFilter, removeFilter, gridStateUpdated} from 'akeneoassetmanager/application/event/search';
import {CompletenessValue} from 'akeneoassetmanager/application/component/asset/index/completeness-filter';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';

const stateToQuery = async (state: EditState): Promise<Query> => {
  return {
    locale: undefined === state.user.catalogLocale ? '' : state.user.catalogLocale,
    channel: undefined === state.user.catalogChannel ? '' : state.user.catalogChannel,
    size: state.grid.query.size,
    page: state.grid.query.page,
    filters: [
      ...state.grid.query.filters,
      {
        field: 'asset_family',
        operator: '=',
        value: state.form.data.identifier,
        context: {},
      },
    ],
  };
};

export const MAX_DISPLAYED_ASSETS = 500;

export const needMoreResults = () => (dispatch: any, getState: any) => {
  if (
    !getState().grid.isFetching &&
    getState().grid.items.length < MAX_DISPLAYED_ASSETS &&
    getState().grid.items.length < getState().grid.matchesCount
  ) {
    dispatch(updateAssetResults(true));
  }
};

export const searchUpdated = (searchInput: string) => (dispatch: any) => {
  dispatch(updateFilter('full_text', '=', searchInput));
  dispatch(updateAssetResults(false));
  dispatch(gridStateUpdated());
};

export const filterUpdated = (updatedFilter: Filter) => (dispatch: any) => {
  dispatch(updateFilter(updatedFilter.field, updatedFilter.operator, updatedFilter.value));
  dispatch(updateAssetResults(false));
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

  dispatch(updateAssetResults(false));
  dispatch(gridStateUpdated());
};

export const updateAssetResults = updateResultsWithFetcher<NormalizedItemAsset>(assetFetcher, stateToQuery);
