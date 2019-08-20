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
