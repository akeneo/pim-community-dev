import AssetFetcher from 'akeneoassetmanager/domain/fetcher/asset';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import EditionAsset from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {getJSON, putJSON} from 'akeneoassetmanager/tools/fetch';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {BackendEditionAsset} from 'akeneoassetmanager/infrastructure/model/edition-asset';
import {BackendListAsset} from 'akeneoassetmanager/infrastructure/model/list-asset';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {validateBackendListAsset} from 'akeneoassetmanager/infrastructure/validator/list-asset';
import {validateBackendEditionAsset} from 'akeneoassetmanager/infrastructure/validator/edition-asset';
import {denormalizeAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

const routing = require('routing');

class InvalidArgument extends Error {}

export type AssetResult = {
  asset: EditionAsset;
  permission: AssetFamilyPermission;
};

const denormalizeEditionAsset = (assetFamily: AssetFamily) => (backendAsset: BackendEditionAsset): EditionAsset => {
  return {
    identifier: backendAsset.identifier,
    code: backendAsset.code,
    createdAt: backendAsset.created_at,
    updatedAt: backendAsset.updated_at,
    labels: denormalizeLabelCollection(backendAsset.labels),
    assetFamily,
    values: backendAsset.values.map(backendEditionValue => ({
      ...backendEditionValue,
      attribute: denormalizeAttribute(backendEditionValue.attribute),
    })),
  };
};

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

export class AssetFetcherImplementation implements AssetFetcher {
  private assetsByCodesCache: {
    [key: string]: Promise<SearchResult<ListAsset>>;
  } = {};

  async fetch(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<AssetResult> {
    const [assetData, assetFamilyResult] = await Promise.all([
      await getJSON(
        routing.generate('akeneo_asset_manager_asset_get_rest', {
          assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
          assetCode: assetCodeStringValue(assetCode),
        })
      ).catch(errorHandler),
      assetFamilyFetcher.fetch(denormalizeAssetFamilyIdentifier(assetFamilyIdentifier)),
    ]);

    const backendEditionAsset = validateBackendEditionAsset(assetData);
    const editionAsset = denormalizeEditionAsset(assetFamilyResult.assetFamily)(backendEditionAsset);

    return {
      asset: editionAsset,
      permission: {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        edit: assetData.permission.edit,
      },
    };
  }

  async search(query: Query): Promise<SearchResult<ListAsset>> {
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

  async fetchByCodes(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCodes: AssetCode[],
    context: {
      channel: string;
      locale: string;
    },
    cached: boolean = false
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
    if (!cached || undefined === this.assetsByCodesCache[queryHash]) {
      this.assetsByCodesCache[queryHash] = this.search(query);
    }

    return (await this.assetsByCodesCache[queryHash]).items;
  }
}

export default new AssetFetcherImplementation();
