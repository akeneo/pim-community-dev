import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetResult} from 'akeneoassetmanager/infrastructure/fetcher/asset';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';

export default interface Fetcher {
  fetch: (assetFamilyIdentifier: AssetFamilyIdentifier, identifier: AssetCode) => Promise<AssetResult>;
  search: (query: Query) => Promise<{items: ListAsset[]; matchesCount: number}>;
}
