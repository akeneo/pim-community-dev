import LabelCollection, {
  denormalizeLabelCollection,
  getLabelInCollection,
  emptyLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AssetIdentifier, {
  assetFamilyidentifiersAreEqual,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

export interface AssetFamilyCreation {
  code: AssetIdentifier;
  labels: LabelCollection;
}

export const createEmptyAssetFamilyCreation = (): AssetFamilyCreation => ({
  code: '',
  labels: emptyLabelCollection(),
});
export const createAssetFamilyCreationFromNormalized = (normalizedAssetFamilyCreation: any): AssetFamilyCreation => ({
  code: denormalizeAssetFamilyIdentifier(normalizedAssetFamilyCreation.code),
  labels: denormalizeLabelCollection(normalizedAssetFamilyCreation.labels),
});
export const getAssetFamilyCreationLabel = (
  assetFamily: AssetFamilyCreation,
  locale: LocaleCode,
  fallbackOnCode: boolean = true
): string => {
  return getLabelInCollection(assetFamily.labels, locale, fallbackOnCode, assetFamily.code);
};
export const assetFamilyCreationAreEqual = (first: AssetFamilyCreation, second: AssetFamilyCreation): boolean =>
  assetFamilyidentifiersAreEqual(first.code, second.code);
