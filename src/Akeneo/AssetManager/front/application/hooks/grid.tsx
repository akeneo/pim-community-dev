import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Context} from 'akeneoassetmanager/domain/model/context';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {Query, SearchResult, emptySearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {Selection} from 'akeneo-design-system';

const MAX_RESULT = 500;
const FIRST_PAGE_SIZE = 50;

type AssetDataProvider = {
  assetFetcher: {
    search: (query: Query) => Promise<SearchResult<ListAsset>>;
  };
};

export const createQuery = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  filters: Filter[],
  searchValue: string,
  _excludedAssetCollection: AssetCode[],
  channel: ChannelCode,
  locale: LocaleCode,
  page: number,
  size: number
): Query => ({
  locale,
  channel,
  size,
  page,
  filters: [
    ...filters,
    {
      field: 'asset_family',
      operator: '=',
      value: assetFamilyIdentifier,
      context: {},
    },
    {
      field: 'full_text',
      operator: '=',
      value: searchValue,
      context: {},
    },
  ],
});

export const addSelection = (query: Query, selection: Selection): Query => ({
  ...query,
  filters: [
    ...query.filters,
    {
      field: 'code',
      operator: selection.mode === 'in' ? 'IN' : 'NOT IN',
      value: selection.collection,
      context: {}
    }
  ]
});

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
  const executeQuery = () => {
    if (!isOpen || null === assetFamilyIdentifier) {
      setSearchResult(emptySearchResult());
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
  };

  React.useEffect(executeQuery, [
    filters,
    searchValue,
    context,
    excludedAssetCollection,
    isOpen,
    assetFamilyIdentifier,
  ]);

  return () => executeQuery();
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
