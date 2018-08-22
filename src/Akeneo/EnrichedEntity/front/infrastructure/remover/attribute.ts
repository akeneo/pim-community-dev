import AttributeRemover from 'akeneoenrichedentity/domain/remover/attribute';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

const routing = require('routing');

export class AttributeRemoverImplementation implements AttributeRemover<AttributeIdentifier> {
  constructor() {
    Object.freeze(this);
  }

  async remove(attributeIdentifier: AttributeIdentifier): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate(
        'akeneo_enriched_entities_attribute_delete_rest',
        {
          attributeIdentifier: attributeIdentifier.normalize().identifier,
          enrichedEntityIdentifier: attributeIdentifier.normalize().enrichedEntityIdentifier
        }
      )
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }
}

export default new AttributeRemoverImplementation();
