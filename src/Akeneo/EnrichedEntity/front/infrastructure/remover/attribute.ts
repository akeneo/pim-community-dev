import AttributeRemover from 'akeneoenrichedentity/domain/remover/attribute';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';

const routing = require('routing');

export class AttributeRemoverImplementation implements AttributeRemover<EnrichedEntityIdentifier, AttributeIdentifier> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    attributeIdentifier: AttributeIdentifier
  ): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_enriched_entities_attribute_delete_rest', {
        enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
        attributeIdentifier: attributeIdentifier.normalize(),
      })
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }
}

export default new AttributeRemoverImplementation();
