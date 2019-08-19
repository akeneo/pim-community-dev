import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {LocaleCode, ChannelCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createCode} from 'akeneoassetmanager/domain/model/code';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {NormalizedFile} from 'akeneoassetmanager/domain/model/file';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {NormalizedAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

export type AssetFamilyIdentifier = string;
export type AssetIdentifier = string;
export type Labels = {
  [locale: string]: string;
};
type AssetFamilyCode = string;
type AttributeIdentifier = string;
type Value = NormalizedValue;
type Image = string;

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

export const fetchAssetByCodes = async (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  codes: AssetCode[],
  context: {channel: ChannelCode; locale: LocaleCode}
): Promise<Asset[]> => {
  const [assets, assetFamilyResult] = await Promise.all([
    assetFetcher.fetchByCodes(createIdentifier(assetFamilyIdentifier), codes.map(createCode), context),
    assetFamilyFetcher.fetch(createIdentifier(assetFamilyIdentifier)),
  ]);

  const assetFamily = denormalizeAssetFamily(assetFamilyResult.assetFamily.normalize());

  return assets.map(
    (asset: NormalizedItemAsset): Asset => {
      return {
        ...asset,
        assetFamily: assetFamily,
      };
    }
  );
};

const denormalizeAssetFamily = (normalizedAssetFamily: NormalizedAssetFamily): AssetFamily => {
  return {
    identifier: normalizedAssetFamily.identifier,
    code: normalizedAssetFamily.code,
    labels: normalizedAssetFamily.labels,
    image: normalizedAssetFamily.image,
    attributeAsLabel: normalizedAssetFamily.attribute_as_label,
    attributeAsImage: normalizedAssetFamily.attribute_as_image,
  };
};

export const getImage = (asset: Asset): Image => {
  return asset.image;
};

export const isComplete = (asset: Asset) => asset.completeness.complete === asset.completeness.required;
