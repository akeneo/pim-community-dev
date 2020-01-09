import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamilyResult} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';

export interface AssetFamilyFetcher {
  fetch: (identifier: AssetFamilyIdentifier) => Promise<AssetFamilyResult>;
  fetchAll: () => Promise<AssetFamilyListItem[]>;
  search: (query: Query) => Promise<SearchResult<AssetFamilyListItem>>;
}
