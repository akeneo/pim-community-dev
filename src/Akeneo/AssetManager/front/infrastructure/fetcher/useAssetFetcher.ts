import {useRouter} from '@akeneo-pim-community/shared';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import EditionAsset from 'akeneoassetmanager/domain/model/asset/edition-asset';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
import {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {BackendEditionAsset} from 'akeneoassetmanager/infrastructure/model/edition-asset';
import {BackendListAsset} from 'akeneoassetmanager/infrastructure/model/list-asset';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {validateBackendListAsset} from 'akeneoassetmanager/infrastructure/validator/list-asset';
import {validateBackendEditionAsset} from 'akeneoassetmanager/infrastructure/validator/edition-asset';
import {denormalizeAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';
import {useAssetFamilyFetcher} from './useAssetFamilyFetcher';
import {AssetFetcher, AssetResult} from 'akeneoassetmanager/domain/fetcher/asset';

class InvalidArgument extends Error {}

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

let assetsByCodesCache: {[key: string]: Promise<SearchResult<ListAsset>>} = {};

const fetchAssetData = async (route: string): Promise<any> => {
  const response = await fetch(route);

  return await handleResponse(response);
};

const useAssetFetcher = (): AssetFetcher => {
  const router = useRouter();
  const assetFamilyFetcher = useAssetFamilyFetcher();

  const fetchAsset = async (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCode: AssetCode
  ): Promise<AssetResult> => {
    const route = router.generate('akeneo_asset_manager_asset_get_rest', {
      assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
      assetCode: assetCodeStringValue(assetCode),
    });

    const [assetData, assetFamilyResult] = await Promise.all([
      fetchAssetData(route),
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
  };

  const search = async (query: Query): Promise<SearchResult<ListAsset>> => {
    const assetFamilyCodeFilter = query.filters.find((filter: Filter) => 'asset_family' === filter.field);
    if (undefined === assetFamilyCodeFilter) {
      throw new InvalidArgument('The search repository expects a asset_family filter');
    }

    const route = router.generate('akeneo_asset_manager_asset_index_rest', {
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
  };

  const fetchByCodes = async (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCodes: AssetCode[],
    context: {
      channel: string;
      locale: string;
    },
    cached: boolean = false
  ): Promise<ListAsset[]> => {
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
      assetsByCodesCache[queryHash] = search(query);
    }

    return (await assetsByCodesCache[queryHash]).items;
  };

  return {fetch: fetchAsset, search, fetchByCodes};
};

export {useAssetFetcher};
