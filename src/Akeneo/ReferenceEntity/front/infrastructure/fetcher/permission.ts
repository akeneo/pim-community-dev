import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {
  denormalizePermissionCollection,
  NormalizedPermission,
  PermissionCollection,
} from 'akeneoreferenceentity/domain/model/reference-entity/permission';

const routing = require('routing');

export interface PermissionFetcher {
  fetch: (identifier: ReferenceEntityIdentifier) => Promise<PermissionCollection>;
}

export class PermissionFetcherImplementation implements PermissionFetcher {
  async fetch(identifier: ReferenceEntityIdentifier): Promise<PermissionCollection> {
    const backendPermissions = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_permission_get_rest', {
        referenceEntityIdentifier: identifier.stringValue(),
      })
    ).catch(errorHandler);

    return denormalizePermissionCollection(backendPermissions as NormalizedPermission[]);
  }
}

export default new PermissionFetcherImplementation();
