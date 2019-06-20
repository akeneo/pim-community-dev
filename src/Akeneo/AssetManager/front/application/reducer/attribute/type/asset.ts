import {NormalizedAssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import {NormalizedAssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';

const assetAttributeReducer = (
  normalizedAttribute: NormalizedAssetAttribute | NormalizedAssetCollectionAttribute
): NormalizedAssetAttribute | NormalizedAssetCollectionAttribute => {
  // Nothing to edit
  return normalizedAttribute;
};

export const reducer = assetAttributeReducer;
