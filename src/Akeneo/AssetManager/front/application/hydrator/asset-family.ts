import {AssetFamily, createAssetFamilyFromNormalized} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';

export const hydrator = (createAssetFamilyFromNormalized: (normalizedAssetFamily: AssetFamily) => AssetFamily) => (
  backendAssetFamily: any
): AssetFamily => {
  const expectedKeys = ['identifier', 'labels', 'image', 'attribute_as_image', 'attribute_as_label'];

  validateKeys(backendAssetFamily, expectedKeys, 'The provided raw asset family seems to be malformed.');
  return createAssetFamilyFromNormalized(backendAssetFamily);
};

export default hydrator(createAssetFamilyFromNormalized);
