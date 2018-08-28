import Saver from 'akeneoenrichedentity/domain/saver/attribute';
import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import MinimalAttribute from 'akeneoenrichedentity/domain/model/attribute/minimal';
import handleError from 'akeneoenrichedentity/infrastructure/saver/error-handler';

const routing = require('routing');

export interface AttributeSaver extends Saver<MinimalAttribute, Attribute> {}

export class AttributeSaverImplementation implements AttributeSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as any; //Todo: remove when backend remove is_text_area
    normalizedAttribute.is_text_area = normalizedAttribute.is_textarea;
    return await postJSON(
      routing.generate('akeneo_enriched_entities_attribute_edit_rest', {
        enrichedEntityIdentifier: attribute.getEnrichedEntityIdentifier().stringValue(),
        attributeIdentifier: attribute.getIdentifier().identifier,
      }),
      normalizedAttribute
    ).catch(handleError);
  }

  async create(attribute: MinimalAttribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize();

    return await postJSON(
      routing.generate('akeneo_enriched_entities_attribute_create_rest', {
        enrichedEntityIdentifier: attribute.getEnrichedEntityIdentifier().stringValue(),
      }),
      normalizedAttribute
    ).catch(handleError);
  }
}

export default new AttributeSaverImplementation();
