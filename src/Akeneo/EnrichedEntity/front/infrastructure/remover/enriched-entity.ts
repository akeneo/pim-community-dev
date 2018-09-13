import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Remover from 'akeneoenrichedentity/domain/remover/remover';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

const routing = require('routing');

export interface EnrichedEntityRemover extends Remover<EnrichedEntityIdentifier> {}

export class EnrichedEntityRemoverImplementation implements EnrichedEntityRemover {
  constructor() {
    Object.freeze(this);
  }

  async remove(attributeIdentifier: EnrichedEntityIdentifier): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_enriched_entities_enriched_entity_delete_rest', {
        identifier: attributeIdentifier.stringValue(),
      })
    ).catch(errorHandler);
  }
}

export default new EnrichedEntityRemoverImplementation();
