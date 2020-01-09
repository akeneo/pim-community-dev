import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Context} from 'akeneoassetmanager/domain/model/context';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';

const MAX_RESULT = 500;
const FIRST_PAGE_SIZE = 50;

type AssetDataProvider = {
  assetFetcher: {
    search: (query: Query) => Promise<SearchResult<ListAsset>>;
  };
};

let totalRequestCount = 0;
export const useFetchResult = (
  createQuery: (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    filters: Filter[],
    searchValue: string,
    excludedAssetCollection: AssetCode[],
    channel: ChannelCode,
    locale: LocaleCode,
    page: number,
    size: number
  ) => Query
) => (
  isOpen: boolean,
  dataProvider: AssetDataProvider,
  assetFamilyIdentifier: AssetFamilyIdentifier | null,
  filters: Filter[],
  searchValue: string,
  excludedAssetCollection: AssetCode[],
  context: Context,
  setSearchResult: (result: SearchResult<ListAsset>) => void
) => {
  const [askForReload, setAskForReload] = React.useState(false);
  React.useEffect(() => {
    setAskForReload(false);
    if (!isOpen || null === assetFamilyIdentifier) {
      return;
    }

    const query = createQuery(
      assetFamilyIdentifier,
      filters,
      searchValue,
      excludedAssetCollection,
      context.channel,
      context.locale,
      0,
      FIRST_PAGE_SIZE
    );
    totalRequestCount++;

    dataProvider.assetFetcher.search(query).then((searchResult: SearchResult<ListAsset>) => {
      const currentRequestCount = totalRequestCount;
      setSearchResult(searchResult);
      if (searchResult.matchesCount > FIRST_PAGE_SIZE) {
        fetchMoreResult(currentRequestCount, dataProvider)(query, setSearchResult);
      }
    });
  }, [filters, searchValue, context, excludedAssetCollection, isOpen, assetFamilyIdentifier, askForReload]);

  return () => setAskForReload(true);
};

const fetchMoreResult = (currentRequestCount: number, dataProvider: AssetDataProvider) => (
  query: Query,
  setSearchResult: (result: SearchResult<ListAsset>) => void
) => {
  dataProvider.assetFetcher.search({...query, size: MAX_RESULT}).then((searchResult: SearchResult<ListAsset>) => {
    if (currentRequestCount === totalRequestCount) {
      setSearchResult(searchResult);
    }
  });
};
