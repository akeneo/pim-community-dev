import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier from 'akeneoassetmanager/domain/model/asset/identifier';
import ListValue, {ListValueCollection, getListValue} from 'akeneoassetmanager/domain/model/asset/list-value';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {MediaPreview, MediaPreviewType, emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData, MediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {getLabel} from 'pimui/js/i18n';
import {assetCodesAreEqual} from 'akeneoassetmanager/domain/model/asset-family/code';
import {isValueEmpty} from 'akeneoassetmanager/domain/model/asset/value';

export const ASSET_COLLECTION_LIMIT = 50;

export enum MoveDirection {
  Before,
  After,
}

export type ValueCollection = {[key: string]: ListValue};

export const createEmptyAsset = (assetCode?: AssetCode): ListAsset => ({
  identifier: '',
  code: assetCode || '',
  labels: {},
  image: [],
  assetFamilyIdentifier: '',
  completeness: {
    complete: 0,
    required: 0,
  },
  values: {},
});

//TODO refactor image naming
type ListAsset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  image: ListValueCollection;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  values: ValueCollection;
  completeness: NormalizedCompleteness;
};

export const getListAssetMediaData = (asset: ListAsset, channel: ChannelCode, locale: LocaleCode): MediaData => {
  const value = getListValue(asset.image, channel, locale);

  return value ? (value.data as MediaData) : null;
};

export const getListAssetMainMediaThumbnail = (
  asset: ListAsset,
  channel: ChannelCode,
  locale: LocaleCode,
  previewType: MediaPreviewType = MediaPreviewType.Thumbnail
): MediaPreview => {
  const listValue = getListValue(asset.image, channel, locale);
  if (undefined === listValue || isValueEmpty(listValue)) {
    return emptyMediaPreview();
  }

  return {
    type: previewType,
    attributeIdentifier: listValue.attribute,
    data: getMediaData(listValue.data),
  };
};

export const assetHasCompleteness = (asset: ListAsset) => asset.completeness.required > 0;

export const isComplete = (asset: ListAsset) => asset.completeness.complete === asset.completeness.required;

export const getAssetLabel = (asset: ListAsset, locale: LocaleCode): string => {
  return getLabel(asset.labels, locale, asset.code);
};

export const canAddAssetToCollection = (assetCollection: AssetCode[]): boolean => {
  return assetCollection.length < ASSET_COLLECTION_LIMIT;
};

export const addAssetToCollection = (assetCollection: AssetCode[], codeToAdd: AssetCode): AssetCode[] => {
  return [...assetCollection, codeToAdd];
};

export const addAssetsToCollection = (assetCollection: AssetCode[], assetCodes: AssetCode[]): AssetCode[] => {
  return [...assetCollection, ...assetCodes];
};

export const removeAssetFromAssetCodeCollection = (
  assetCollection: AssetCode[],
  assetCodeToRemove: AssetCode
): AssetCode[] => {
  return assetCollection.filter((assetCode: AssetCode) => assetCodeToRemove !== assetCode);
};

export const removeAssetFromAssetCollection = (
  assetCollection: ListAsset[],
  assetCodeToRemove: AssetCode
): ListAsset[] => {
  return assetCollection.filter((asset: ListAsset) => assetCodeToRemove !== asset.code);
};

export const isAssetInCollection = (assetCodeToLocate: AssetCode, assetCollection: AssetCode[]): boolean => {
  return assetCollection.some((assetCode: AssetCode) => assetCodesAreEqual(assetCodeToLocate, assetCode));
};

export const emptyCollection = (_assetCollection: AssetCode[]): AssetCode[] => {
  return [];
};

export const getPreviousAssetCode = (assetCollection: AssetCode[], assetCode: AssetCode): AssetCode => {
  const currentAssetPosition = assetCollection.indexOf(assetCode);

  return assetCollection[(assetCollection.length + currentAssetPosition - 1) % assetCollection.length];
};

export const getNextAssetCode = (assetCollection: AssetCode[], assetCode: AssetCode): AssetCode => {
  const currentAssetPosition = assetCollection.indexOf(assetCode);

  return assetCollection[(currentAssetPosition + 1) % assetCollection.length];
};

export const assetWillNotMoveInCollection = (
  assetCollection: ListAsset[],
  asset: ListAsset,
  direction: MoveDirection
): boolean => {
  const currentAssetPosition = assetCollection.indexOf(asset);

  return (
    (0 === currentAssetPosition && direction === MoveDirection.Before) ||
    (assetCollection.length - 1 === currentAssetPosition && direction === MoveDirection.After) ||
    -1 === currentAssetPosition
  );
};

export const getAssetCodes = (assetCollection: ListAsset[]): AssetCode[] => {
  return assetCollection.map(asset => asset.code);
};

export const moveAssetInCollection = (
  assetCollection: ListAsset[],
  asset: ListAsset,
  direction: MoveDirection
): ListAsset[] => {
  const currentAssetPosition = assetCollection.indexOf(asset);

  // If asset already first, last or doesn't exists we do nothing
  if (assetWillNotMoveInCollection(assetCollection, asset, direction)) {
    return assetCollection;
  }

  const newAssetPosition = direction === MoveDirection.Before ? currentAssetPosition - 1 : currentAssetPosition + 1;

  return direction === MoveDirection.Before
    ? [
        ...assetCollection.slice(0, newAssetPosition), // Beginning of the array
        assetCollection[currentAssetPosition], // Swap
        assetCollection[newAssetPosition], // Swap
        ...assetCollection.slice(currentAssetPosition + 1, assetCollection.length), // End of the array
      ]
    : [
        ...assetCollection.slice(0, currentAssetPosition), // Beginning of the array
        assetCollection[newAssetPosition], // Swap
        assetCollection[currentAssetPosition], // Swap
        ...assetCollection.slice(newAssetPosition + 1, assetCollection.length), // End of the array
      ];
};

export const getAssetByCode = <T extends ListAsset = ListAsset>(
  assetCollection: T[],
  assetCode: AssetCode
): T | undefined => {
  return assetCollection.find((asset: ListAsset) => asset.code === assetCode);
};

export const sortAssetCollection = <T extends ListAsset = ListAsset>(
  assetCollection: T[],
  assetCodes: AssetCode[]
): T[] => {
  return [...assetCollection].sort((a, b) => assetCodes.indexOf(a.code) - assetCodes.indexOf(b.code));
};

export default ListAsset;
