import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createCode} from 'akeneoassetmanager/domain/model/code';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {NormalizedAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ChannelCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {LocaleCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {
  Asset,
  AssetFamilyIdentifier,
  AssetFamily,
  validateLabels,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';

export const fetchAssetByCodes = async (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  codes: AssetCode[],
  context: {channel: ChannelCode; locale: LocaleCode}
): Promise<Asset[]> => {
  const [assetsResult, assetFamilyResult] = await Promise.all([
    assetFetcher.fetchByCodes(createIdentifier(assetFamilyIdentifier), codes.map(createCode), context),
    assetFamilyFetcher.fetch(createIdentifier(assetFamilyIdentifier)),
  ]);

  return denormalizeAssetCollection(assetsResult, assetFamilyResult);
};

const denormalizeAssetCollection = (assets: any, assetFamilyResult: any): Asset[] => {
  if (!Array.isArray(assets)) {
    throw Error('not a valid asset collection');
  }

  if (undefined === assetFamilyResult.assetFamily) {
    throw Error('not a valid assetFamily');
  }

  const assetFamily = denormalizeAssetFamily(assetFamilyResult.assetFamily.normalize());

  return assets.map(
    (asset: NormalizedItemAsset): Asset => {
      return {
        ...denormalizeAsset(asset),
        assetFamily: assetFamily,
      };
    }
  );
};

const denormalizeAsset = (asset: any): NormalizedItemAsset => {
  if (asset.identifier === undefined || typeof asset.identifier !== 'string') {
    throw Error('The identifier is not well formated');
  }

  if (asset.asset_family_identifier === undefined || typeof asset.asset_family_identifier !== 'string') {
    throw Error('The asset family identifier is not well formated');
  }

  if (asset.code === undefined || typeof asset.code !== 'string') {
    throw Error('The code is not well formated');
  }

  if (asset.completeness === undefined || !validateNormalizedCompleteness(asset.completeness)) {
    throw Error('The completeness is not well formated');
  }

  if (asset.image === undefined || typeof asset.image !== 'string') {
    throw Error('The image is not well formated');
  }

  if (asset.labels === undefined || !validateLabels(asset.labels)) {
    throw Error('The labels is not well formated');
  }

  return asset;
};

const validateNormalizedCompleteness = (completeness: any): boolean => {
  if (typeof completeness !== 'object') {
    return false;
  }

  if (completeness.complete === undefined || typeof completeness.complete !== 'number') {
    return false;
  }

  if (completeness.required === undefined || typeof completeness.required !== 'number') {
    return false;
  }

  return true;
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
