import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  getLabelInCollection,
  denormalizeLabelCollection,
  emptyLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import {File, createFileFromNormalized, createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import AssetFamilyCode from 'akeneoassetmanager/domain/model/asset-family/code';
import TransformationCollection, {
  denormalizeAssetFamilyTransformations,
} from 'akeneoassetmanager/domain/model/asset-family/transformation/transformation-collection';

export interface AssetFamily {
  identifier: AssetFamilyIdentifier;
  code: AssetFamilyCode;
  labels: LabelCollection;
  image: File;
  attributeAsLabel: AttributeIdentifier;
  attributeAsMainMedia: AttributeIdentifier;
  transformations: TransformationCollection;
}

export const createEmptyAssetFamily = (): AssetFamily => ({
  identifier: '',
  code: '',
  labels: emptyLabelCollection(),
  image: createEmptyFile(),
  attributeAsMainMedia: '',
  attributeAsLabel: '',
  transformations: '[]',
});
export const createAssetFamilyFromNormalized = (normalizedAssetFamily: any): AssetFamily => ({
  identifier: denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier),
  code: denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier),
  labels: denormalizeLabelCollection(normalizedAssetFamily.labels),
  image: createFileFromNormalized(normalizedAssetFamily.image),
  attributeAsMainMedia: denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_main_media),
  attributeAsLabel: denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_label),
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
