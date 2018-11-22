import {postJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {AttributeWithOptions, OptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import {Option} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';

const routing = require('routing');

export class AttributeOptionSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedOptionAttribute = (attribute as OptionAttribute).normalize() as any;

    return await postJSON(
      routing.generate('akeneo_reference_entities_attribute_edit_rest', {
        referenceEntityIdentifier: (attribute as OptionAttribute).getReferenceEntityIdentifier().stringValue(),
        attributeIdentifier: (attribute as OptionAttribute).getIdentifier().identifier,
      }),
      {
        identifier: normalizedOptionAttribute.identifier,
        reference_entity_identifier: normalizedOptionAttribute.reference_entity_identifier,
        options: ((attribute as any) as AttributeWithOptions).getOptions().map((option: Option) => option.normalize()),
      }
    ).catch(handleError);
  }
}

const attributeOptionSaver = new AttributeOptionSaver();
export default attributeOptionSaver;
