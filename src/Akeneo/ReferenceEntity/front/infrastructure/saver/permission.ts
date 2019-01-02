import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {PermissionCollection} from 'web/bundles/akeneoreferenceentity/domain/model/reference-entity/permission';
import {postJSON} from 'akeneoreferenceentity/tools/fetch';

const routing = require('routing');

export interface ReferenceEntitySaver {
  save: (
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    permissions: PermissionCollection
  ) => Promise<ValidationError[] | null>;
}

export class PermissionSaverImplementation implements ReferenceEntitySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    permissions: PermissionCollection
  ): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_reference_entities_reference_entity_permission_set_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
      }),
      permissions.normalize()
    ).catch(handleError);
  }
}

export default new PermissionSaverImplementation();
