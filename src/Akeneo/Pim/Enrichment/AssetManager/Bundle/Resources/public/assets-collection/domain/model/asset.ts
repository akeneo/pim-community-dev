import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {
  AssetFamily,
  emptyAssetFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {assetcodesAreEqual} from 'akeneoassetmanager/domain/model/asset/code';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';

export const ASSET_COLLECTION_LIMIT = 50;

export enum MoveDirection {
  Before,
  After,
}

export type AssetIdentifier = string;

type ImageCollection = ImageValue[];

export type ImageData = {filePath: string; originalFilename: string};
export type ImageValue = {
  attribute: AttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
  data: ImageData;
};
const createEmptyImage = (): ImageValue => ({
  attribute: '',
  channel: null,
  locale: null,
  data: {filePath: '', originalFilename: ''},
});
const createEmptyImageCollection = (): ImageCollection => [createEmptyImage()];
export type Completeness = NormalizedCompleteness;

export const assetHasCompleteness = (asset: Asset) => asset.completeness.required > 0;
export const getCompletenessPercentage = (completeness: Completeness) =>
  Math.floor((completeness.complete / completeness.required) * 100);

export type Asset = ListAsset & {
  assetFamily: AssetFamily;
};

export const isComplete = (asset: ListAsset) => asset.completeness.complete === asset.completeness.required;
export const emptyAsset = (assetCode?: AssetCode): Asset => ({
  identifier: '',
  code: assetCode || '',
  labels: {},
  image: createEmptyImageCollection(),
  assetFamily: emptyAssetFamily(),
  completeness: {
    complete: 0,
    required: 0,
  },
  values: {},
  assetFamilyIdentifier: '',
});

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
  return assetCollection.some((assetCode: AssetCode) => assetcodesAreEqual(assetCodeToLocate, assetCode));
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

export const getAssetByCode = <T extends Asset = Asset>(assetCollection: T[], assetCode: AssetCode): T | undefined => {
  return assetCollection.find((asset: ListAsset) => asset.code === assetCode);
};

export const sortAssetCollection = <T extends Asset = Asset>(assetCollection: T[], assetCodes: AssetCode[]): T[] => {
  return [...assetCollection].sort((a, b) => assetCodes.indexOf(a.code) - assetCodes.indexOf(b.code));
};

// //TODO clean
// const getAssetMainMedia = (asset: Asset, _context: Context): ImageValue | undefined => asset.image[0];

// export const assetHasMainMedia = (asset: Asset, context: Context): asset is Asset => {
//   const image = getAssetMainMedia(asset, context);

//   return undefined !== image && image.data.filePath !== '';
// };

// //TODO clean
// export const getAssetMainMediaDownloadLink = (asset: Asset, context: Context): string => {
//   const imageValue = getAssetMainMedia(asset, context) as ImageValue;
//   const attribute = getAttribute(asset.assetFamily.attributes, imageValue.attribute);

//   return MEDIA_LINK_ATTRIBUTE_TYPE === attribute.type
//     ? getMediaLinkUrl(imageValue.data, attribute as NormalizedMediaLinkAttribute)
//     : getMediaDownloadUrl(imageValue.data.filePath);
// };

// //TODO clean
// export const getAssetMainMediaOriginalFilename = (asset: Asset, context: Context) =>
//   (getAssetMainMedia(asset, context) as ImageValue).data.originalFilename;

// const getAttribute = (
//   attributes: NormalizedAttribute[],
//   attributeIdentifier: AttributeIdentifier
// ): NormalizedAttribute => {
//   const attribute = attributes.find((attribute: NormalizedAttribute) => attribute.identifier === attributeIdentifier);

//   if (undefined === attribute) {
//     throw Error(`Attribute "${attributeIdentifier}" doesn't seem to exist`);
//   }

//   return attribute;
// };

// // export const getAttributeAsMainMedia = (asset: Asset): NormalizedAttribute =>
// //   getAttribute(asset.assetFamily.attributes, asset.assetFamily.attributeAsMainMedia);

// const getMediaLinkUrl = (image: ImageData, attribute: NormalizedMediaLinkAttribute): string => {
//   switch (attribute.media_type) {
//     case MediaTypes.youtube:
//       return YOUTUBE_WATCH_URL + image.originalFilename;
//     default:
//       return `${null !== attribute.prefix ? attribute.prefix : ''}${image.filePath}${
//         null !== attribute.suffix ? attribute.suffix : ''
//       }`;
//   }
// };
