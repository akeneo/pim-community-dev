import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

type AttributeFetcher = {
  fetchAll: (assetFamilyIdentifier: AssetFamilyIdentifier) => Promise<Attribute[]>;
};

export {AttributeFetcher};
