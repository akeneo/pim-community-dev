import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {
  denormalizePermissionCollection,
  NormalizedPermission,
  PermissionCollection,
} from 'akeneoassetmanager/domain/model/asset-family/permission';

export interface PermissionFetcher {
  fetch: (identifier: AssetFamilyIdentifier) => Promise<PermissionCollection>;
}

const generatePermissionGetUrl = (identifier: AssetFamilyIdentifier) => `/rest/asset_manager/${identifier}/permissions`;

export class PermissionFetcherImplementation implements PermissionFetcher {
  async fetch(identifier: AssetFamilyIdentifier): Promise<PermissionCollection> {
    const response = await fetch(generatePermissionGetUrl(assetFamilyIdentifierStringValue(identifier))).catch(
      errorHandler
    );

    const backendPermissions = await response.json();

    return denormalizePermissionCollection(backendPermissions as NormalizedPermission[]);
  }
}

export default new PermissionFetcherImplementation();
