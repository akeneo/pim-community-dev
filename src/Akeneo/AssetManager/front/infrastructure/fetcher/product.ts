import ProductFetcher from 'akeneoassetmanager/domain/fetcher/product';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import hydrator from 'akeneoassetmanager/application/hydrator/product';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import {getJSON} from 'akeneoassetmanager/tools/fetch';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import Product from 'akeneoassetmanager/domain/model/product/product';
import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import ChannelReference, {channelReferenceStringValue} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {attributeCodeStringValue} from 'akeneoassetmanager/domain/model/attribute/code';

const routing = require('routing');

export class ProductFetcherImplementation implements ProductFetcher {
  async fetchLinkedProducts(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCode: AssetCode,
    attributeCode: AttributeCode,
    channel: ChannelReference,
    locale: LocaleReference
  ): Promise<SearchResult<Product>> {
    const backendProducts = await getJSON(
      routing.generate('akeneo_asset_manager_product_get_linked_product', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        assetCode: assetCodeStringValue(assetCode),
        attributeCode: attributeCodeStringValue(attributeCode),
        channel: channelReferenceStringValue(channel),
        locale: localeReferenceStringValue(locale),
      })
    ).catch(errorHandler);

    return {
      items: hydrateAll<Product>(hydrator)(backendProducts.items, {locale, channel}),
      matchesCount: backendProducts.items.length,
      totalCount: backendProducts.total_count,
    };
  }
}

export default new ProductFetcherImplementation();
