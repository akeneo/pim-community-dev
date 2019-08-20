import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {NormalizedFile} from 'akeneoassetmanager/domain/model/file';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';

export type AssetFamilyIdentifier = string;
export type AssetIdentifier = string;

type AssetFamilyCode = string;
type AttributeIdentifier = string;
type Value = NormalizedValue;
type Image = string;

const emptyAssetFamily = (): AssetFamily => ({
  identifier: '',
  code: '',
  image: {
    filePath: '',
    originalFilename: '',
  },
  labels: {},
  attributeAsLabel: '',
  attributeAsImage: '',
});
export type AssetFamily = {
  identifier: AssetFamilyIdentifier;
  code: AssetFamilyCode;
  labels: Labels;
  image: NormalizedFile;
  attributeAsLabel: AttributeIdentifier;
  attributeAsImage: AttributeIdentifier;
};
export type Asset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  image: Image;
  assetFamily: AssetFamily;
  labels: Labels;
  values: Value[];
  completeness: NormalizedCompleteness;
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
  values: [],
  completeness: {
    complete: 0,
    required: 0,
  },
});
