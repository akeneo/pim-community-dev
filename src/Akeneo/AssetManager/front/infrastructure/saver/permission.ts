import {ValidationError} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {PermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateAssetPermissionEditUrl = (assetFamilyIdentifier: AssetFamilyIdentifier) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/permissions`;

export interface PermissionSaver {
  save: (
    assetFamilyIdentifier: AssetFamilyIdentifier,
    permissions: PermissionCollection
  ) => Promise<ValidationError[] | null>;
}

export class PermissionSaverImplementation implements PermissionSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    permissions: PermissionCollection
  ): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateAssetPermissionEditUrl(assetFamilyIdentifierStringValue(assetFamilyIdentifier)),
      {
        method: 'POST',
        cache: 'no-cache',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(permissions.normalize()),
      }
    );

    return await handleResponse(response);
  }
}

const permissionSaver = new PermissionSaverImplementation();
export default permissionSaver;
