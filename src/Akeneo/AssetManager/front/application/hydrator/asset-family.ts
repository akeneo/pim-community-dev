import AssetFamily, {
  denormalizeAssetFamily,
  NormalizedAssetFamily,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {validateKeys} from 'akeneoassetmanager/application/hydrator/hydrator';

export const hydrator = (denormalizeAssetFamily: (normalizedAssetFamily: NormalizedAssetFamily) => AssetFamily) => (
  backendAssetFamily: any
): AssetFamily => {
  const expectedKeys = ['identifier', 'labels', 'image', 'attribute_as_image', 'attribute_as_label'];

  validateKeys(backendAssetFamily, expectedKeys, 'The provided raw asset family seems to be malformed.');
  return denormalizeAssetFamily(backendAssetFamily);
};

export default hydrator(denormalizeAssetFamily);
