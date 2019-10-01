import Asset, {NormalizedAsset, createAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createValueCollection} from 'akeneoassetmanager/domain/model/asset/value-collection';
import denormalizeValue from 'akeneoassetmanager/application/denormalizer/asset/value';
import {denormalizeFile} from 'akeneoassetmanager/domain/model/file';
import {denormalizeAssetIdentifier} from 'akeneoassetmanager/domain/model/asset/identifier';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';

const denormalizeAsset = (normalizedAsset: NormalizedAsset): Asset => {
  const identifier = denormalizeAssetIdentifier(normalizedAsset.identifier);
  const code = denormalizeAssetCode(normalizedAsset.code);
  const assetFamilyIdentifier = denormalizeAssetFamilyIdentifier(normalizedAsset.asset_family_identifier);
  const labelCollection = createLabelCollection(normalizedAsset.labels);
  const image = denormalizeFile(normalizedAsset.image);
  const valueCollection = createValueCollection(normalizedAsset.values.map(denormalizeValue));

  return createAsset(identifier, assetFamilyIdentifier, code, labelCollection, image, valueCollection);
};

export default denormalizeAsset;
