import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {
  AssetFamily,
  emptyAssetFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {assetcodesAreEqual} from 'akeneoassetmanager/domain/model/asset/code';

export enum MoveDirection {
  Before,
  After,
}

export type AssetIdentifier = string;

type Image = string;
export type Completeness = NormalizedCompleteness;

export type Asset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  image: Image;
  assetFamily: AssetFamily;
  labels: Labels;
  completeness: Completeness;
};

export const getImage = (asset: Asset): Image => {
  return asset.image;
};

export const isComplete = (asset: Asset) => asset.completeness.complete === asset.completeness.required;
export const emptyAsset = (assetCode?: AssetCode): Asset => ({
  identifier: '',
  code: assetCode || '',
  labels: {},
  image: '',
  assetFamily: emptyAssetFamily(),
  completeness: {
    complete: 0,
    required: 0,
  },
});

export const getAssetLabel = (asset: Asset, locale: LocaleCode) => {
  return getLabel(asset.labels, locale, asset.code);
};

export const addAssetToCollection = (assetCollection: AssetCode[], codeToAdd: AssetCode) => [
  ...assetCollection,
  codeToAdd,
];

export const addAssetsToCollection = (assetCollection: AssetCode[], assetCodes: AssetCode[]): AssetCode[] => {
  return [...assetCollection, ...assetCodes];
};

export const removeAssetFromCollection = (assetCodes: AssetCode[], assetCodeToRemove: AssetCode): AssetCode[] => {
  return assetCodes.filter((assetCode: AssetCode) => assetCodeToRemove !== assetCode);
};

export const isAssetInCollection = (assetCodeToLocate: AssetCode, assetCollection: AssetCode[]): boolean => {
  return assetCollection.some((assetCode: AssetCode) => assetcodesAreEqual(assetCodeToLocate, assetCode));
};

export const emptyCollection = (_assetCodes: AssetCode[]): AssetCode[] => {
  return [];
};

export const assetWillNotMoveInCollection = (
  assetCodes: AssetCode[],
  asset: Asset,
  direction: MoveDirection
): boolean => {
  const currentAssetPosition = assetCodes.indexOf(asset.code);

  return (
    (0 === currentAssetPosition && direction === MoveDirection.Before) ||
    (assetCodes.length - 1 === currentAssetPosition && direction === MoveDirection.After) ||
    -1 === currentAssetPosition
  );
};

export const getAssetCodes = (assetCollection: Asset[]): AssetCode[] => {
  return assetCollection.map(asset => asset.code);
};

export const moveAssetInCollection = (assetCodes: AssetCode[], asset: Asset, direction: MoveDirection): AssetCode[] => {
  const currentAssetPosition = assetCodes.indexOf(asset.code);

  //If asset already first, last or doesn't exists we do nothing
  if (assetWillNotMoveInCollection(assetCodes, asset, direction)) {
    return assetCodes;
  }

  const newAssetPosition = direction === MoveDirection.Before ? currentAssetPosition - 1 : currentAssetPosition + 1;

  return direction === MoveDirection.Before
    ? [
        ...assetCodes.slice(0, newAssetPosition), // Begining of the array
        assetCodes[currentAssetPosition], // Swap
        assetCodes[newAssetPosition], // Swap
        ...assetCodes.slice(currentAssetPosition + 1, assetCodes.length), // End of the array
      ]
    : [
        ...assetCodes.slice(0, currentAssetPosition), // Begining of the array
        assetCodes[newAssetPosition], // Swap
        assetCodes[currentAssetPosition], // Swap
        ...assetCodes.slice(newAssetPosition + 1, assetCodes.length), // End of the array
      ];
};

export const getAssetByCode = (assetCollection: Asset[], assetCode: AssetCode): Asset | undefined => {
  return assetCollection.find((asset: Asset) => asset.code === assetCode);
};
