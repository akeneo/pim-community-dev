import {useRouter} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {hydrator} from 'akeneoassetmanager/application/hydrator/attribute';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {validateBackendAttribute} from 'akeneoassetmanager/infrastructure/validator/attribute';
import {useAttributeDenormalizer} from 'akeneoassetmanager/application/hooks/attribute/useAttributeDenormalizer';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {handleResponse} from '../tools/handleResponse';

const useAttributeFetcher = () => {
  const router = useRouter();
  const attributeDenormalizer = useAttributeDenormalizer();
  const fetchAllNormalized = async (assetFamilyIdentifier: AssetFamilyIdentifier): Promise<NormalizedAttribute[]> => {
    const route = router.generate('akeneo_asset_manager_attribute_index_rest', {
      assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
    });

    const response = await fetch(route);
    const backendAttributes = await handleResponse(response);

    return backendAttributes.map(validateBackendAttribute);
  };

  const fetchAll = async (assetFamilyIdentifier: AssetFamilyIdentifier): Promise<Attribute[]> => {
    const backendAttributes = await fetchAllNormalized(assetFamilyIdentifier);

    return hydrateAll<Attribute>(hydrator(attributeDenormalizer))(backendAttributes);
  };

  return {fetchAllNormalized, fetchAll};
};

export {useAttributeFetcher};
