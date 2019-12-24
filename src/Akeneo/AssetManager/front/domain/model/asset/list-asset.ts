import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier from 'akeneoassetmanager/domain/model/asset/identifier';
import ListValue, {getPreviewModel} from 'akeneoassetmanager/domain/model/asset/list-value';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceStringValue,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceStringValue,
} from 'akeneoassetmanager/domain/model/locale-reference';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {MediaPreview, MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import MediaFileData from 'akeneoassetmanager/domain/model/asset/data/media-file';
import MediaLinkData from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {getLabel} from 'pimui/js/i18n';
import {assetCodesAreEqual} from 'akeneoassetmanager/domain/model/asset-family/code';

export const ASSET_COLLECTION_LIMIT = 50;

export enum MoveDirection {
  Before,
  After,
}

export type ValueCollection = {[key: string]: ListValue};

export type PreviewCollection = PreviewModel[];

export type PreviewModel = ListValue & {
  data: MediaFileData | MediaLinkData;
};

export const creatEmptyAsset = (assetCode?: AssetCode): ListAsset => ({
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
  image: PreviewCollection;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  values: ValueCollection;
  completeness: NormalizedCompleteness;
};

export const generateKey = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
): string => {
  let key = attributeIdentifierStringValue(attributeIdentifier);
  key = !channelReferenceIsEmpty(channel) ? `${key}_${channelReferenceStringValue(channel)}` : key;
  key = !localeReferenceIsEmpty(locale) ? `${key}_${localeReferenceStringValue(locale)}` : key;

  return key;
};

export const generateValueKey = (value: ListValue) => generateKey(value.attribute, value.channel, value.locale);

export const getListAssetMainMediaThumbnail = (
  asset: ListAsset,
  channel: ChannelCode,
  locale: LocaleCode
): MediaPreview => {
  const previewModel = getPreviewModel(asset.image, channel, locale);
  if (undefined === previewModel) {
    return {
      type: MediaPreviewType.Thumbnail,
      attributeIdentifier: 'UNKNOWN',
      data: '',
    };
  }
  const attributeIdentifier = previewModel.attribute;

  return {
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier,
    data: getMediaData(previewModel.data),
  };
};

const previewModelIsUndefined = (previewModel: any): previewModel is undefined => previewModel === undefined;
const previewModelIsNull = (previewModel: any): previewModel is null => previewModel === null;

export const isMainMediaEmpty = (asset: ListAsset, channel: ChannelCode, locale: LocaleCode) => {
  const previewModel = getPreviewModel(asset.image, channel, locale);

  return previewModelIsUndefined(previewModel) || previewModelIsNull(previewModel.data);
};

export const assetHasCompleteness = (asset: ListAsset) => asset.completeness.required > 0;
export const getCompletenessPercentage = (completeness: NormalizedCompleteness) =>
  Math.floor((completeness.complete / completeness.required) * 100);

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

export const removeAssetFromCollection = (assetCollection: AssetCode[], assetCodeToRemove: AssetCode): AssetCode[] => {
  return assetCollection.filter((assetCode: AssetCode) => assetCodeToRemove !== assetCode);
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
  assetCollection: AssetCode[],
  asset: ListAsset,
  direction: MoveDirection
): boolean => {
  const currentAssetPosition = assetCollection.indexOf(asset.code);

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
  assetCollection: AssetCode[],
  asset: ListAsset,
  direction: MoveDirection
): AssetCode[] => {
  const currentAssetPosition = assetCollection.indexOf(asset.code);

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
