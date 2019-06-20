import ProductFetcher from 'akeneoassetmanager/domain/fetcher/product';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import hydrator from 'akeneoassetmanager/application/hydrator/product';
import hydrateAll from 'akeneoassetmanager/application/hydrator/hydrator';
import {getJSON} from 'akeneoassetmanager/tools/fetch';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import Product from 'akeneoassetmanager/domain/model/product/product';
import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import {SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';

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
        assetFamilyIdentifier: assetFamilyIdentifier.stringValue(),
        assetCode: assetCode.stringValue(),
        attributeCode: attributeCode.stringValue(),
        channel: channel.stringValue(),
        locale: locale.stringValue(),
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
