// import {postJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {PermissionConfiguration} from 'akeneoreferenceentity/tools/component/permission';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

// const routing = require('routing');

export interface ReferenceEntitySaver {
  save: (
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    permission: PermissionConfiguration
  ) => Promise<ValidationError[] | null>;
}

export class PermissionSaverImplementation implements ReferenceEntitySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(
    _referenceEntityIdentifier: ReferenceEntityIdentifier,
    _permission: PermissionConfiguration
  ): Promise<ValidationError[] | null> {
    return await Promise.resolve(null).catch(handleError);
  }
}

export default new PermissionSaverImplementation();
