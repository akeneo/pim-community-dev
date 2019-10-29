import {getJSON} from 'akeneoassetmanager/tools/fetch';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {
  denormalizePermissionCollection,
  NormalizedPermission,
  PermissionCollection,
} from 'akeneoassetmanager/domain/model/asset-family/permission';

const routing = require('routing');

export interface PermissionFetcher {
  fetch: (identifier: AssetFamilyIdentifier) => Promise<PermissionCollection>;
}

export class PermissionFetcherImplementation implements PermissionFetcher {
  async fetch(identifier: AssetFamilyIdentifier): Promise<PermissionCollection> {
    const backendPermissions = await getJSON(
      routing.generate('akeneo_asset_manager_asset_family_permission_get_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(identifier),
      })
    ).catch(errorHandler);

    return denormalizePermissionCollection(backendPermissions as NormalizedPermission[]);
  }
}

export default new PermissionFetcherImplementation();
