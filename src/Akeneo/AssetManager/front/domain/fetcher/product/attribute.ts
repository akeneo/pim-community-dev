import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import Attribute from 'akeneoassetmanager/domain/model/product/attribute';

export default interface ProductAttributeFetcher {
  fetchLinkedAssetAttributes: (assetFamilyIdentifier: AssetFamilyIdentifier) => Promise<Attribute[]>;
}
