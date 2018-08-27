import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Remover from 'akeneoenrichedentity/domain/remover/remover';

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
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }
}

export default new EnrichedEntityRemoverImplementation();
