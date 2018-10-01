import AttributeRemover from 'akeneoreferenceentity/domain/remover/attribute';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import {deleteJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export class AttributeRemoverImplementation
  implements AttributeRemover<ReferenceEntityIdentifier, AttributeIdentifier> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    attributeIdentifier: AttributeIdentifier
  ): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_reference_entities_attribute_delete_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        attributeIdentifier: attributeIdentifier.normalize(),
      })
    ).catch(errorHandler);
  }
}

export default new AttributeRemoverImplementation();
