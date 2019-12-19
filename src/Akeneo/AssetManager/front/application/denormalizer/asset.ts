import Asset, {NormalizedAsset, createAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import denormalizeValue from 'akeneoassetmanager/application/denormalizer/asset/value';
import {denormalizeAssetIdentifier} from 'akeneoassetmanager/domain/model/asset/identifier';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';

const denormalizeAsset = (normalizedAsset: NormalizedAsset): Asset => {
  const identifier = denormalizeAssetIdentifier(normalizedAsset.identifier);
  const code = denormalizeAssetCode(normalizedAsset.code);
  const assetFamilyIdentifier = denormalizeAssetFamilyIdentifier(normalizedAsset.asset_family_identifier);
  const attributeAsMainMediaIdentifier = denormalizeAttributeIdentifier(
    normalizedAsset.attribute_as_main_media_identifier
  );
  const labelCollection = denormalizeLabelCollection(normalizedAsset.labels);
  const valueCollection = normalizedAsset.values.map(denormalizeValue);
  const image = normalizedAsset.image.map(denormalizeValue);

  return createAsset(
    identifier,
    assetFamilyIdentifier,
    attributeAsMainMediaIdentifier,
    code,
    labelCollection,
    image,
    valueCollection
  );
};

export default denormalizeAsset;
