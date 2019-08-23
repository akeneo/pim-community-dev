import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
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

  const assets = validateNormalizedItemAssetCollection(assetsResult);
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

const validateNormalizedItemAssetCollection = (assets: any): NormalizedItemAsset[] => {
  if (!Array.isArray(assets)) {
    throw Error('not a valid asset collection');
  }

  return assets.map(asset => validateNormalizedItemAsset(asset));
};

const validateNormalizedItemAsset = (asset: any): NormalizedItemAsset => {
  if (asset.identifier === undefined || typeof asset.identifier !== 'string') {
    throw Error('The identifier is not a valid property');
  }

  if (asset.asset_family_identifier === undefined || typeof asset.asset_family_identifier !== 'string') {
    throw Error('The asset family identifier is not a valid property');
  }

  if (asset.code === undefined || typeof asset.code !== 'string') {
    throw Error('The code is not a valid property');
  }

  if (asset.completeness === undefined || !validateNormalizedCompleteness(asset.completeness)) {
    throw Error('The completeness is not a valid property');
  }

  if (asset.image === undefined || typeof asset.image !== 'string') {
    throw Error('The image is not a valid property');
  }

  if (asset.labels === undefined || typeof asset.labels !== 'object') {
    throw Error('The labels is not a valid property');
  }

  if (asset.values === undefined || typeof asset.values !== 'object') {
    throw Error('The values is not a valid property');
  }

  return asset;
};

const validateNormalizedCompleteness = (completeness: any): NormalizedCompleteness => {
  if (completeness.complete === undefined || typeof completeness.complete !== 'number') {
    throw Error('The completeness.complete is not a valid property');
  }

  if (completeness.required === undefined || typeof completeness.required !== 'number') {
    throw Error('The completeness.required is not a valid property');
  }

  return completeness;
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
