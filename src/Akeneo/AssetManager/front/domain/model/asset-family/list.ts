import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
  emptyLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AssetIdentifier, {
  assetFamilyidentifiersAreEqual,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {File, createFileFromNormalized, createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

export interface AssetFamilyListItem {
  identifier: AssetIdentifier;
  labels: LabelCollection;
  image: File;
}

export const createEmptyAssetFamilyListItem = (): AssetFamilyListItem => ({
  identifier: '',
  labels: emptyLabelCollection(),
  image: createEmptyFile(),
});
export const createAssetFamilyListItemFromNormalized = (normalizedAssetFamilyListItem: any): AssetFamilyListItem => ({
  identifier: denormalizeAssetFamilyIdentifier(normalizedAssetFamilyListItem.identifier),
  labels: denormalizeLabelCollection(normalizedAssetFamilyListItem.labels),
  image: createFileFromNormalized(normalizedAssetFamilyListItem.image),
});
export const getAssetFamilyListItemLabel = (
  assetFamily: AssetFamilyListItem,
  locale: LocaleCode,
  fallbackOnCode: boolean = true
): string => {
  return getLabelInCollection(assetFamily.labels, locale, fallbackOnCode, assetFamily.identifier);
};
export const assetFamilyListItemAreEqual = (first: AssetFamilyListItem, second: AssetFamilyListItem): boolean =>
  assetFamilyidentifiersAreEqual(first.identifier, second.identifier);
