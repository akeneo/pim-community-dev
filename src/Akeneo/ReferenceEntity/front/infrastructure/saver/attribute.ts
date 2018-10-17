import Saver from 'akeneoreferenceentity/domain/saver/attribute';
import {postJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import MinimalAttribute from 'akeneoreferenceentity/domain/model/attribute/minimal';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

const routing = require('routing');

export interface AttributeSaver extends Saver<MinimalAttribute, Attribute> {}

export class AttributeSaverImplementation implements AttributeSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as any;
    normalizedAttribute.identifier = {
      identifier: normalizedAttribute.identifier,
      reference_entity_identifier: normalizedAttribute.reference_entity_identifier,
    };

    return await postJSON(
      routing.generate('akeneo_reference_entities_attribute_edit_rest', {
        referenceEntityIdentifier: attribute.getReferenceEntityIdentifier().stringValue(),
        attributeIdentifier: attribute.getIdentifier().identifier,
      }),
      attribute.normalize()
    ).catch(handleError);
  }

  async create(attribute: MinimalAttribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as NormalizedAttribute;

    return await postJSON(
      routing.generate('akeneo_reference_entities_attribute_create_rest', {
        referenceEntityIdentifier: attribute.getReferenceEntityIdentifier().stringValue(),
      }),
      normalizedAttribute
    ).catch(handleError);
  }
}

export default new AttributeSaverImplementation();
