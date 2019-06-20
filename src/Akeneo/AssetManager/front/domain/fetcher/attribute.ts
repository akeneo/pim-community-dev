import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

export default interface Fetcher {
  fetchAll: (assetFamilyIdentifier: AssetFamilyIdentifier) => Promise<Attribute[]>;
}
