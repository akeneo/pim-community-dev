// import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';
// import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {
  denormalizePermissionCollection,
  NormalizedPermission,
  PermissionCollection,
} from 'akeneoreferenceentity/domain/model/reference-entity/permission';

// const routing = require('routing');

export interface PermissionFetcher {
  fetch: (identifier: ReferenceEntityIdentifier) => Promise<PermissionCollection>;
}

export class PermissionFetcherImplementation implements PermissionFetcher {
  async fetch(identifier: ReferenceEntityIdentifier): Promise<PermissionCollection> {
    // const backendPermissions = await getJSON(
    //   routing.generate('akeneo_reference_entities_reference_entity_permission_get_rest', {
    //     referenceEntityIdentifier: identifier.stringValue(),
    //   })
    // ).catch(errorHandler);

    let backendPermissions = [
      {
        user_group_name: 'It manager',
        user_group_identifier: 12,
        right_level: 'view',
      },
      {
        user_group_name: 'admin',
        user_group_identifier: 44,
        right_level: 'edit',
      },
      {
        user_group_name: 'Translator',
        user_group_identifier: 85,
        right_level: 'view',
      },
    ];

    return denormalizePermissionCollection(backendPermissions as NormalizedPermission[]);
  }
}

export default new PermissionFetcherImplementation();
