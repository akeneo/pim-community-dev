import {IndexState} from 'akeneoassetmanager/application/reducer/asset-family/index';
import AssetFamilyListItem from 'akeneoassetmanager/domain/model/asset-family/list';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import AssetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import updateResultsWithFetcher from 'akeneoassetmanager/application/action/search';

const stateToQuery = async (state: IndexState): Promise<Query> => {
  return {
    locale: undefined === state.user.catalogLocale ? '' : state.user.catalogLocale,
    channel: undefined === state.user.catalogChannel ? '' : state.user.catalogChannel,
    size: state.grid.query.size,
    page: state.grid.query.page,
    filters: [],
  };
};

export const updateAssetFamilyResults = updateResultsWithFetcher<AssetFamilyListItem>(
  AssetFamilyFetcher,
  stateToQuery
);
