import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {
  denormalizePermissionCollection,
  NormalizedPermission,
  PermissionCollection,
} from 'akeneoassetmanager/domain/model/asset-family/permission';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

export interface PermissionFetcher {
  fetch: (identifier: AssetFamilyIdentifier) => Promise<PermissionCollection>;
}

const generatePermissionGetUrl = (identifier: AssetFamilyIdentifier) => `/rest/asset_manager/${identifier}/permissions`;

export class PermissionFetcherImplementation implements PermissionFetcher {
  async fetch(identifier: AssetFamilyIdentifier): Promise<PermissionCollection> {
    const response = await fetch(generatePermissionGetUrl(assetFamilyIdentifierStringValue(identifier)));

    const backendPermissions = await handleResponse(response);

    return denormalizePermissionCollection(backendPermissions as NormalizedPermission[]);
  }
}

export default new PermissionFetcherImplementation();
