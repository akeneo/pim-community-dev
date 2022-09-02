import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Product} from 'akeneoassetmanager/domain/model/product/product';
import {ProductAttributeCode} from 'akeneoassetmanager/domain/model/product/attribute';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import {SearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';

export default interface Fetcher {
  fetchLinkedProducts: (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    assetCode: AssetCode,
    attributeCode: ProductAttributeCode,
    channel: ChannelReference,
    locale: LocaleReference
  ) => Promise<SearchResult<Product>>;
}
