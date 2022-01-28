import {useRouter} from '@akeneo-pim-community/shared';
import hydrator from 'akeneoassetmanager/application/hydrator/asset-family';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {hydrator as attributeHydrator} from 'akeneoassetmanager/application/hydrator/attribute';
import {validateBackendAssetFamily} from 'akeneoassetmanager/infrastructure/validator/asset-family';
import {
  AssetFamilyListItem,
  createAssetFamilyListItemFromNormalized,
} from 'akeneoassetmanager/domain/model/asset-family/list';
import {AssetFamilyResult} from 'akeneoassetmanager/domain/fetcher/asset-family';
import {useAttributeDenormalizer} from '../../application/hooks/attribute/useAttributeDenormalizer';
import {handleResponse} from '../tools/handleResponse';

const useAssetFamilyFetcher = () => {
  const router = useRouter();
  const attributeDenormalizer = useAttributeDenormalizer();

  const fetchAssetFamily = async (identifier: AssetFamilyIdentifier): Promise<AssetFamilyResult> => {
    const response = await fetch(
      router.generate('akeneo_asset_manager_asset_family_get_rest', {
        identifier: assetFamilyIdentifierStringValue(identifier),
      })
    );

    const responseJson = await handleResponse(response);
    const backendAssetFamily = validateBackendAssetFamily(responseJson);

    return {
      assetFamily: hydrator(backendAssetFamily),
      assetCount: backendAssetFamily.asset_count,
      attributes: backendAssetFamily.attributes.map(attributeHydrator(attributeDenormalizer)),
      permission: {
        assetFamilyIdentifier: identifier,
        edit: backendAssetFamily.permission.edit,
      },
    };
  };

  const fetchAll = async (): Promise<AssetFamilyListItem[]> => {
    const response = await fetch(router.generate('akeneo_asset_manager_asset_family_index_rest'));

    const backendAssetFamilies = await handleResponse(response);

    return hydrateAll<AssetFamilyListItem>(createAssetFamilyListItemFromNormalized)(backendAssetFamilies.items);
  };

  return {fetch: fetchAssetFamily, fetchAll} as const;
};

export {useAssetFamilyFetcher};
