import AssetFamilyIdentifier, {
  assetFamilyidentifiersAreEqual,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  denormalizeLabelCollection,
  emptyLabelCollection,
  getLabelInCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import {createEmptyFile, createFileFromNormalized, File} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import AssetFamilyCode from 'akeneoassetmanager/domain/model/asset-family/code';
import TransformationCollection, {
  denormalizeAssetFamilyTransformations,
} from 'akeneoassetmanager/domain/model/asset-family/transformation/transformation-collection';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

export interface AssetFamily {
  identifier: AssetFamilyIdentifier;
  code: AssetFamilyCode;
  labels: LabelCollection;
  image: File;
  attributeAsLabel: AttributeIdentifier;
  attributeAsMainMedia: AttributeIdentifier;
  attributes: NormalizedAttribute[];
  transformations: TransformationCollection;
}

export const createEmptyAssetFamily = (): AssetFamily => ({
  identifier: '',
  code: '',
  labels: emptyLabelCollection(),
  image: createEmptyFile(),
  attributeAsMainMedia: '',
  attributeAsLabel: '',
  attributes: [],
  transformations: '[]',
});

export const createAssetFamilyFromNormalized = (normalizedAssetFamily: any): AssetFamily => ({
  identifier: denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier),
  code: denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier),
  labels: denormalizeLabelCollection(normalizedAssetFamily.labels),
  image: createFileFromNormalized(normalizedAssetFamily.image),
  attributeAsMainMedia: denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_main_media),
  attributeAsLabel: denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_label),
  attributes: normalizedAssetFamily.attributes,
  transformations: denormalizeAssetFamilyTransformations(normalizedAssetFamily.transformations),
});

export const getAssetFamilyLabel = (
  assetFamily: AssetFamily,
  locale: LocaleCode,
  fallbackOnCode: boolean = true
): string => {
  return getLabelInCollection(assetFamily.labels, locale, fallbackOnCode, assetFamily.code);
};

export const assetFamilyAreEqual = (first: AssetFamily, second: AssetFamily): boolean =>
  assetFamilyidentifiersAreEqual(first.identifier, second.identifier);

export const getAssetFamilyMainMedia = (assetFamily: AssetFamily): NormalizedAttribute => {
  const attribute = assetFamily.attributes.find(
    (attribute: NormalizedAttribute) => attribute.identifier === assetFamily.attributeAsMainMedia
  );
  if (undefined === attribute) {
    throw new Error('The AssetFamily must have an attribute as main media');
  }
  return attribute;
};
