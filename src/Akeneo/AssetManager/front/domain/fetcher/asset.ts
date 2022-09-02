import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import EditionAsset from '../model/asset/edition-asset';
import {AssetFamilyPermission} from '../model/permission/asset-family';

type AssetResult = {
  asset: EditionAsset;
  permission: AssetFamilyPermission;
};

type AssetFetcher = {
  fetch: (assetFamilyIdentifier: AssetFamilyIdentifier, identifier: AssetCode) => Promise<AssetResult>;
  search: (query: Query) => Promise<SearchResult<ListAsset>>;
  fetchByCodes: (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCodes: AssetCode[],
    context: {
      channel: string;
      locale: string;
    },
    cached?: boolean
  ) => Promise<ListAsset[]>;
};

export {AssetFetcher, AssetResult};
