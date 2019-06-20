import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import hydrator from 'akeneoassetmanager/application/hydrator/asset-family';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import {getJSON} from 'akeneoassetmanager/tools/fetch';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/identifier';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import hydrateAttribute from 'akeneoassetmanager/application/hydrator/attribute';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
import AssetFamilyListItem, {denormalizeAssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';

const routing = require('routing');

export interface AssetFamilyFetcher {
  fetch: (identifier: AssetFamilyIdentifier) => Promise<AssetFamilyResult>;
  fetchAll: () => Promise<AssetFamilyListItem[]>;
  search: (query: Query) => Promise<SearchResult<AssetFamilyListItem>>;
}

export type AssetFamilyResult = {
  assetFamily: AssetFamily;
  assetCount: number;
  attributes: Attribute[];
  permission: AssetFamilyPermission;
};

export class AssetFamilyFetcherImplementation implements AssetFamilyFetcher {
  async fetch(identifier: AssetFamilyIdentifier): Promise<AssetFamilyResult> {
    const backendAssetFamily = await getJSON(
      routing.generate('akeneo_asset_manager_asset_family_get_rest', {identifier: identifier.stringValue()})
    ).catch(errorHandler);

    return {
      assetFamily: hydrator(backendAssetFamily),
      assetCount: backendAssetFamily.asset_count,
      attributes: backendAssetFamily.attributes.map((normalizedAttribute: NormalizedAttribute) =>
        hydrateAttribute(normalizedAttribute)
      ),
      permission: {
        assetFamilyIdentifier: identifier.stringValue(),
        edit: backendAssetFamily.permission.edit,
      },
    };
  }

  async fetchAll(): Promise<AssetFamilyListItem[]> {
    const backendAssetFamilies = await getJSON(routing.generate('akeneo_asset_manager_asset_family_index_rest')).catch(
      errorHandler
    );

    return hydrateAll<AssetFamilyListItem>(denormalizeAssetFamilyListItem)(backendAssetFamilies.items);
  }

  async search(): Promise<SearchResult<AssetFamilyListItem>> {
    const backendAssetFamilies = await getJSON(routing.generate('akeneo_asset_manager_asset_family_index_rest')).catch(
      errorHandler
    );

    const items = hydrateAll<AssetFamilyListItem>(denormalizeAssetFamilyListItem)(backendAssetFamilies.items);

    return {
      items,
      matchesCount: backendAssetFamilies.matchesCount,
      totalCount: backendAssetFamilies.matchesCount,
    };
  }
}

export default new AssetFamilyFetcherImplementation();
