import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {deleteJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Remover from 'akeneoreferenceentity/domain/remover/remover';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export interface ReferenceEntityRemover extends Remover<ReferenceEntityIdentifier> {}

export class ReferenceEntityRemoverImplementation implements ReferenceEntityRemover {
  constructor() {
    Object.freeze(this);
  }

  async remove(attributeIdentifier: ReferenceEntityIdentifier): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_reference_entities_reference_entity_delete_rest', {
        identifier: attributeIdentifier.stringValue(),
      })
    ).catch(errorHandler);
  }
}

export default new ReferenceEntityRemoverImplementation();
