import Asset, {NormalizedAsset, createAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {createIdentifier} from 'akeneoassetmanager/domain/model/asset/identifier';
import {createIdentifier as createAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createCode} from 'akeneoassetmanager/domain/model/asset/code';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createValueCollection} from 'akeneoassetmanager/domain/model/asset/value-collection';
import denormalizeValue from 'akeneoassetmanager/application/denormalizer/asset/value';
import {denormalizeFile} from 'akeneoassetmanager/domain/model/file';

const denormalizeAsset = (normalizedAsset: NormalizedAsset): Asset => {
  const identifier = createIdentifier(normalizedAsset.identifier);
  const code = createCode(normalizedAsset.code);
  const assetFamilyIdentifier = createAssetFamilyIdentifier(normalizedAsset.asset_family_identifier);
  const labelCollection = createLabelCollection(normalizedAsset.labels);
  const image = denormalizeFile(normalizedAsset.image);
  const valueCollection = createValueCollection(normalizedAsset.values.map(denormalizeValue));

  return createAsset(identifier, assetFamilyIdentifier, code, labelCollection, image, valueCollection);
};

export default denormalizeAsset;
