import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {AssetFamilyPermission} from 'akeneoassetmanager/domain/model/permission/asset-family';

type AssetFamilyResult = {
  assetFamily: AssetFamily;
  assetCount: number;
  attributes: Attribute[];
  permission: AssetFamilyPermission;
};

type AssetFamilyFetcher = {
  fetch: (identifier: AssetFamilyIdentifier) => Promise<AssetFamilyResult>;
  fetchAll: () => Promise<AssetFamilyListItem[]>;
};

export {AssetFamilyFetcher, AssetFamilyResult};
