import ProductFetcher from 'akeneoassetmanager/domain/fetcher/product';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import hydrator from 'akeneoassetmanager/application/hydrator/product';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import {Product} from 'akeneoassetmanager/domain/model/product/product';
import {ProductAttributeCode} from 'akeneoassetmanager/domain/model/product/attribute';
import ChannelReference, {channelReferenceStringValue} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {attributeCodeStringValue} from 'akeneoassetmanager/domain/model/attribute/code';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateProductListUrl = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  assetCode: AssetCode,
  attributeCode: ProductAttributeCode,
  channelCode: ChannelCode,
  localeCode: LocaleCode
) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/asset/${assetCode}/product/${attributeCode}?channel=${channelCode}&locale=${localeCode}`;

export class ProductFetcherImplementation implements ProductFetcher {
  async fetchLinkedProducts(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCode: AssetCode,
    attributeCode: ProductAttributeCode,
    channel: ChannelReference,
    locale: LocaleReference
  ): Promise<SearchResult<Product>> {
    const response = await fetch(
      generateProductListUrl(
        assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        assetCodeStringValue(assetCode),
        attributeCodeStringValue(attributeCode),
        channelReferenceStringValue(channel),
        localeReferenceStringValue(locale)
      )
    );

    const backendProducts = await handleResponse(response);

    return {
      items: hydrateAll<Product>(hydrator)(backendProducts.items, {locale, channel}),
      matchesCount: backendProducts.items.length,
      totalCount: backendProducts.total_count,
    };
  }
}

export default new ProductFetcherImplementation();
