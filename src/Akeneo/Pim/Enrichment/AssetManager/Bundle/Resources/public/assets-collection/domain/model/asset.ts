import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {
  AssetFamily,
  emptyAssetFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {LocaleCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {getLabel} from 'pimui/js/i18n';

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
export const emptyAsset = (): Asset => ({
  identifier: '',
  code: '',
  labels: {},
  image: '',
  assetFamily: emptyAssetFamily(),
  completeness: {
    complete: 0,
    required: 0,
  },
});
export const isLabels = (labels: any): boolean => {
  if (undefined === labels || typeof labels !== 'object') {
    return false;
  }

  if (Object.keys(labels).some((key: string) => typeof key !== 'string' || typeof labels[key] !== 'string')) {
    return false;
  }

  return true;
};
export const getAssetLabel = (asset: Asset, locale: LocaleCode) => {
  return getLabel(asset.labels, locale, asset.code);
};

export const removeAssetFromCollection = (assetCodes: AssetCode[], asset: Asset): AssetCode[] => {
  return assetCodes.filter((assetCode: AssetCode) => asset.code !== assetCode);
};
export const emptyCollection = (_assetCodes: AssetCode): AssetCode[] => {
  return [];
};
