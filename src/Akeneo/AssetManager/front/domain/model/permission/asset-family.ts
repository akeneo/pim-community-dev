import {NormalizedIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';

export interface AssetFamilyPermission {
  assetFamilyIdentifier: NormalizedIdentifier;
  edit: boolean;
}
