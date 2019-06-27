import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {AssetResult} from 'akeneoassetmanager/infrastructure/fetcher/asset';

export default interface Fetcher {
  fetch: (assetFamilyIdentifier: AssetFamilyIdentifier, identifier: AssetCode) => Promise<AssetResult>;
  search: (query: Query) => Promise<{items: NormalizedItemAsset[]; matchesCount: number}>;
}
