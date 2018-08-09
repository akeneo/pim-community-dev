import Saver from 'akeneoenrichedentity/domain/saver/saver';
import Attribute from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

const routing = require('routing');

export interface AttributeSaver extends Saver<Attribute> {}

export class AttributeSaverImplementation implements AttributeSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as any;
    normalizedAttribute.enriched_entity_identifier = normalizedAttribute.enrichedEntityIdentifier;
    normalizedAttribute.identifier.enriched_entity_identifier = normalizedAttribute.identifier.enrichedEntityIdentifier;
    normalizedAttribute.value_per_locale = normalizedAttribute.valuePerLocale;
    normalizedAttribute.value_per_channel = normalizedAttribute.valuePerChannel;

    delete normalizedAttribute.enrichedEntityIdentifier;
    delete normalizedAttribute.identifier.enrichedEntityIdentifier;
    delete normalizedAttribute.valuePerLocale;
    delete normalizedAttribute.valuePerChannel;

    return await postJSON(
      routing.generate('akeneo_enriched_entities_attribute_edit_rest', {
        enrichedEntityIdentifier: attribute.getEnrichedEntityIdentifier().stringValue(),
        identifier: attribute.getIdentifier().identifier,
      }),
      normalizedAttribute
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }

  async create(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as any;
    normalizedAttribute.enriched_entity_identifier = normalizedAttribute.enrichedEntityIdentifier;
    normalizedAttribute.identifier.enriched_entity_identifier = normalizedAttribute.identifier.enrichedEntityIdentifier;
    normalizedAttribute.value_per_locale = normalizedAttribute.valuePerLocale;
    normalizedAttribute.value_per_channel = normalizedAttribute.valuePerChannel;

    delete normalizedAttribute.enrichedEntityIdentifier;
    delete normalizedAttribute.identifier.enrichedEntityIdentifier;
    delete normalizedAttribute.valuePerLocale;
    delete normalizedAttribute.valuePerChannel;

    return await postJSON(
      routing.generate('akeneo_enriched_entities_attribute_create_rest', {
        enrichedEntityIdentifier: attribute.getEnrichedEntityIdentifier().stringValue(),
      }),
      normalizedAttribute
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }
}

export default new AttributeSaverImplementation();
