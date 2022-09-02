import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {BackendListAsset} from 'akeneoassetmanager/infrastructure/model/list-asset';
import {validateBackendListAsset} from 'akeneoassetmanager/infrastructure/validator/list-asset';

const routing = require('routing');

class InvalidArgument extends Error {}

const denormalizeListAsset = (backendAsset: BackendListAsset): ListAsset => {
  return {
    identifier: backendAsset.identifier,
    code: backendAsset.code,
    labels: denormalizeLabelCollection(backendAsset.labels),
    assetFamilyIdentifier: backendAsset.asset_family_identifier,
    values: Array.isArray(backendAsset.values) ? {} : backendAsset.values,
    image: backendAsset.image,
    completeness: backendAsset.completeness,
  };
};

let assetsByCodesCache: {[key: string]: Promise<SearchResult<ListAsset>>} = {};

type LegacyAssetFetcher = {
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

class LegacyAssetFetcherImplementation implements LegacyAssetFetcher {
  async fetchByCodes(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCodes: AssetCode[],
    context: {channel: string; locale: string},
    cached?: boolean
  ): Promise<ListAsset[]> {
    const query = {
      channel: context.channel,
      locale: context.locale,
      size: 200,
      page: 0,
      filters: [
        {
          field: 'asset_family',
          operator: '=',
          value: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        },
        {
          field: 'code',
          operator: 'IN',
          value: assetCodes.map((assetCode: AssetCode) => assetCodeStringValue(assetCode)),
        },
      ],
    };

    const queryHash = JSON.stringify(query);
    if (!cached || undefined === assetsByCodesCache[queryHash]) {
      assetsByCodesCache[queryHash] = this.search(query);
    }

    return (await assetsByCodesCache[queryHash]).items;
  }

  async search(query: Query): Promise<SearchResult<ListAsset>> {
    const assetFamilyCodeFilter = query.filters.find((filter: Filter) => 'asset_family' === filter.field);
    if (undefined === assetFamilyCodeFilter) {
      throw new InvalidArgument('The search repository expects a asset_family filter');
    }

    const route = routing.generate('akeneo_asset_manager_asset_index_rest', {
      assetFamilyIdentifier: assetFamilyCodeFilter.value,
    });
    const response = await fetch(route, {
      method: 'PUT',
      body: JSON.stringify(query),
      headers: {
        'Content-Type': 'application/json',
      },
    });

    const backendAssets = await response.json();

    const listAssets = backendAssets.items.map((assetData: any) => {
      const backendListAsset = validateBackendListAsset(assetData);
      return denormalizeListAsset(backendListAsset);
    });

    return {
      items: listAssets,
      matchesCount: backendAssets.matches_count,
      totalCount: backendAssets.total_count,
    };
  }
}

export default new LegacyAssetFetcherImplementation();
