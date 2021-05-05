import {ValidationError} from '@akeneo-pim-community/shared';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {PermissionCollection} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {postJSON} from 'akeneoassetmanager/tools/fetch';

const routing = require('routing');

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
    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_family_permission_set_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
      }),
      permissions.normalize()
    ).catch(handleError);
  }
}

export default new PermissionSaverImplementation();
