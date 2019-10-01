import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

export interface AssetFamilyPermission {
  assetFamilyIdentifier: AssetFamilyIdentifier;
  edit: boolean;
}
