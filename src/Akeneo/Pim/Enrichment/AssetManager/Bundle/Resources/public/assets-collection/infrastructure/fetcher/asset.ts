import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {AssetCode} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {AssetFamily} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset-family';
import {Asset, Completeness} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {isNumber, isString, isLabels} from 'akeneoassetmanager/domain/model/utils';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {Query, SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {NormalizedAttribute, Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {createAssetFamilyFromNormalized} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

export const fetchAssetCollection = async (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  codes: AssetCode[],
  context: {channel: ChannelCode; locale: LocaleCode}
): Promise<Asset[]> => {
  const [assetsResult, assetFamilyResult] = await Promise.all([
    assetFetcher.fetchByCodes(assetFamilyIdentifier, codes.map(denormalizeAssetCode), context),
    assetFamilyFetcher.fetch(assetFamilyIdentifier),
  ]);

  return denormalizeAssetCollection(assetsResult, assetFamilyResult);
};

export const searchAssetCollection = async (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  query: Query
): Promise<SearchResult<Asset>> => {
  const [searchResult, assetFamilyResult] = await Promise.all([
    assetFetcher.search(query),
    assetFamilyFetcher.fetch(denormalizeAssetFamilyIdentifier(assetFamilyIdentifier)),
  ]);

  return {...searchResult, items: denormalizeAssetCollection(searchResult.items, assetFamilyResult)};
};

const denormalizeAssetCollection = (assets: any, assetFamilyResult: any): Asset[] => {
  //TODO: we should move this back to asset manager
  if (!Array.isArray(assets)) {
    throw Error('not a valid asset collection');
  }

  if (undefined === assetFamilyResult.assetFamily) {
    throw Error('not a valid assetFamily');
  }

  const assetFamily = denormalizeAssetFamily(
    assetFamilyResult.assetFamily,
    assetFamilyResult.attributes.map((attribute: Attribute) => attribute.normalize())
  );

  return assets.map(
    (asset: NormalizedItemAsset): Asset => {
      return {
        ...denormalizeAsset(asset),
        assetFamily: assetFamily,
      };
    }
  );
};

const denormalizeAsset = (asset: any): Asset => {
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

const denormalizeAssetFamily = (
  normalizedAssetFamily: any,
  attributes: {attributes: NormalizedAttribute & any}[]
): AssetFamily => {
  return {
    ...createAssetFamilyFromNormalized(normalizedAssetFamily),
    attributes,
  };
};
