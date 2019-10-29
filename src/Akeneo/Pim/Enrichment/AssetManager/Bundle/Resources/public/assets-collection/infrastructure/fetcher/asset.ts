import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {NormalizedAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {
  AssetFamilyIdentifier,
  AssetFamily,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {Asset, Completeness} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {isNumber, isString, isLabels} from 'akeneoassetmanager/domain/model/utils';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';

export const fetchAssetCollection = async (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  codes: AssetCode[],
  context: {channel: ChannelCode; locale: LocaleCode}
): Promise<Asset[]> => {
  const [assetsResult, assetFamilyResult] = await Promise.all([
    assetFetcher.fetchByCodes(
      denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
      codes.map(denormalizeAssetCode),
      context
    ),
    assetFamilyFetcher.fetch(denormalizeAssetFamilyIdentifier(assetFamilyIdentifier)),
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
  if (!isString(asset.identifier)) {
    throw Error('The identifier is not well formated');
  }

  if (!isString(asset.asset_family_identifier)) {
    throw Error('The asset family identifier is not well formated');
  }

  if (!isString(asset.code)) {
    throw Error('The code is not well formated');
  }

  if (!isCompleteness(asset.completeness)) {
    throw Error('The completeness is not well formated');
  }

  if (!isLabels(asset.labels)) {
    throw Error('The labels is not well formated');
  }

  return asset;
};

const isCompleteness = (completeness: any): completeness is Completeness => {
  if (undefined === completeness || typeof completeness !== 'object') {
    return false;
  }

  if (!isNumber(completeness.complete)) {
    return false;
  }

  if (!isNumber(completeness.required)) {
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
