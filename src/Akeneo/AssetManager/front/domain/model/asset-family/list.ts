import LabelCollection, {
  denormalizeLabelCollection,
  emptyLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AssetIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {File, createFileFromNormalized, createEmptyFile} from 'akeneoassetmanager/domain/model/file';

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
