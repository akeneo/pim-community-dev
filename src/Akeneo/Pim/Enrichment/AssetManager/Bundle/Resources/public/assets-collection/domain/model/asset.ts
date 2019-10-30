import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {
  AssetFamily,
  emptyAssetFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {assetcodesAreEqual} from 'akeneoassetmanager/domain/model/asset/code';

export const ASSET_COLLECTION_LIMIT = 50;

export enum MoveDirection {
  Before,
  After,
}

export type AssetIdentifier = string;

type ImageCollection = ImageValue[];

type ImageValue = {attribute: ''; channel: null; locale: null; data: {filePath: ''; originalFilename: ''}};
const createEmptyImage = (): ImageValue => ({
  attribute: '',
  channel: null,
  locale: null,
  data: {filePath: '', originalFilename: ''},
});
const createEmptyImageCollection = (): ImageCollection => [createEmptyImage()];
export type Completeness = NormalizedCompleteness;
export const getCompletenessPercentage = (completeness: Completeness) =>
  Math.floor((completeness.complete / completeness.required) * 100);

export type Asset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  image: ImageCollection;
  assetFamily: AssetFamily;
  labels: Labels;
  completeness: Completeness;
};

export const isComplete = (asset: Asset) => asset.completeness.complete === asset.completeness.required;
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
});

export const getAssetLabel = (asset: Asset, locale: LocaleCode): string => {
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
  asset: Asset,
  direction: MoveDirection
): boolean => {
  const currentAssetPosition = assetCollection.indexOf(asset.code);

  return (
    (0 === currentAssetPosition && direction === MoveDirection.Before) ||
    (assetCollection.length - 1 === currentAssetPosition && direction === MoveDirection.After) ||
    -1 === currentAssetPosition
  );
};

export const getAssetCodes = (assetCollection: Asset[]): AssetCode[] => {
  return assetCollection.map(asset => asset.code);
};

export const moveAssetInCollection = (
  assetCollection: AssetCode[],
  asset: Asset,
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

export const getAssetByCode = (assetCollection: Asset[], assetCode: AssetCode): Asset | undefined => {
  return assetCollection.find((asset: Asset) => asset.code === assetCode);
};

export const sortAssetCollection = (assetCollection: Asset[], assetCodes: AssetCode[]): Asset[] => {
  return [...assetCollection].sort((a, b) => assetCodes.indexOf(a.code) - assetCodes.indexOf(b.code));
