import AssetFetcher from 'akeneoassetmanager/domain/fetcher/asset';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import Asset, {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import hydrator from 'akeneoassetmanager/application/hydrator/asset';
import {getJSON, putJSON} from 'akeneoassetmanager/tools/fetch';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
const routing = require('routing');

class InvalidArgument extends Error {}

export type AssetResult = {
  asset: Asset;
  permission: AssetFamilyPermission;
};

export class AssetFetcherImplementation implements AssetFetcher {
  private assetsByCodesCache: {
    [key: string]: Promise<SearchResult<NormalizedItemAsset>>;
  } = {};

  async fetch(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<AssetResult> {
    const backendAsset = await getJSON(
      routing.generate('akeneo_asset_manager_asset_get_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        assetCode: assetCodeStringValue(assetCode),
      })
    ).catch(errorHandler);

    return {
      asset: hydrator(backendAsset),
      permission: {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        edit: backendAsset.permission.edit,
      },
    };
  }

  async search(query: Query): Promise<SearchResult<NormalizedItemAsset>> {
    const assetFamilyCode = query.filters.find((filter: Filter) => 'asset_family' === filter.field);
    if (undefined === assetFamilyCode) {
      throw new InvalidArgument('The search repository expects a asset_family filter');
    }

    const backendAssets = await putJSON(
      routing.generate('akeneo_asset_manager_asset_index_rest', {
        assetFamilyIdentifier: assetFamilyCode.value,
      }),
      query
    ).catch(errorHandler);

    return {
      items: backendAssets.items,
      matchesCount: backendAssets.matches_count,
      totalCount: backendAssets.total_count,
    };
  }

  async fetchByCodes(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCodes: AssetCode[],
    context: {
      channel: string;
      locale: string;
    },
    cached: boolean = false
  ): Promise<NormalizedItemAsset[]> {
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
    if (!cached || undefined === this.assetsByCodesCache[queryHash]) {
      this.assetsByCodesCache[queryHash] = this.search(query);
    }

    return (await this.assetsByCodesCache[queryHash]).items;
  }
}

export default new AssetFetcherImplementation();
