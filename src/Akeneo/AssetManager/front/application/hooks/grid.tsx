import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Context} from 'akeneoassetmanager/domain/model/context';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';

const MAX_RESULT = 500;
const FIRST_PAGE_SIZE = 50;

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
  dataProvider: any,
  assetFamilyIdentifier: AssetFamilyIdentifier | null,
  filters: Filter[],
  searchValue: string,
  excludedAssetCollection: AssetCode[],
  context: Context,
  setResultCollection: (resultCollection: ListAsset[]) => void,
  setResultCount: (count: number) => void
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

    dataProvider.assetFetcher.search(query).then((searchResult: any) => {
      const currentRequestCount = totalRequestCount;
      setResultCollection(searchResult.items);
      setResultCount(searchResult.matchesCount);

      if (searchResult.matchesCount > FIRST_PAGE_SIZE) {
        fetchMoreResult(currentRequestCount, dataProvider)(query, setResultCollection);
      }
    });
  }, [filters, searchValue, context, excludedAssetCollection, isOpen, assetFamilyIdentifier, askForReload]);

  return () => setAskForReload(true);
};

const fetchMoreResult = (currentRequestCount: number, dataProvider: any) => (
  query: Query,
  setResultCollection: (resultCollection: ListAsset[]) => void
) => {
  dataProvider.assetFetcher.search({...query, size: MAX_RESULT}).then((searchResult: any) => {
    if (currentRequestCount === totalRequestCount) {
      setResultCollection(searchResult.items);
    }
  });
};
