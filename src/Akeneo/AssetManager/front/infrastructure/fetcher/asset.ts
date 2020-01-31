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
import ListAsset, {ValueCollection} from 'akeneoassetmanager/domain/model/asset/list-asset';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {PreviewCollection} from 'akeneoassetmanager/domain/model/asset/value';

const routing = require('routing');

class InvalidArgument extends Error {}

export type AssetResult = {
  asset: EditionAsset;
  permission: AssetFamilyPermission;
};

type BackendEditionAsset = {
  identifier: string;
  code: string;
  asset_family_identifier: string;
  labels: LabelCollection;
  values: EditionValue[];
};

const denormalizeEditionAsset = (assetFamily: AssetFamily) => (backendAsset: BackendEditionAsset): EditionAsset => {
  return {
    identifier: backendAsset.identifier,
    code: backendAsset.code,
    labels: backendAsset.labels,
    assetFamily,
    values: backendAsset.values,
  };
};

type BackendListAsset = {
  identifier: string;
  code: string;
  completeness: NormalizedCompleteness;
  asset_family_identifier: string;
  labels: LabelCollection;
  values: ValueCollection;
  image: PreviewCollection;
};

const denormalizeListAsset = (backendAsset: BackendListAsset): ListAsset => {
  return {
    identifier: backendAsset.identifier,
    code: backendAsset.code,
    labels: backendAsset.labels,
    assetFamilyIdentifier: backendAsset.asset_family_identifier,
    values: backendAsset.values,
    image: backendAsset.image,
    completeness: backendAsset.completeness,
  };
};

export class AssetFetcherImplementation implements AssetFetcher {
  private assetsByCodesCache: {
    [key: string]: Promise<SearchResult<ListAsset>>;
  } = {};

  async fetch(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<AssetResult> {
    const [backendAsset, assetFamilyResult] = await Promise.all([
      await getJSON(
        routing.generate('akeneo_asset_manager_asset_get_rest', {
          assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
          assetCode: assetCodeStringValue(assetCode),
        })
      ).catch(errorHandler),
      assetFamilyFetcher.fetch(denormalizeAssetFamilyIdentifier(assetFamilyIdentifier)),
    ]);

    //TODO add json schema validation
    const asset = denormalizeEditionAsset(assetFamilyResult.assetFamily)(backendAsset);

    return {
      asset,
      permission: {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        edit: backendAsset.permission.edit,
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

    // TODO check json schema validation

    return {
      items: backendAssets.items.map(denormalizeListAsset),
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
