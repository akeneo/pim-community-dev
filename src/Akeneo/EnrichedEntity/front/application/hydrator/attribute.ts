import Attribute, {
  denormalizeAttribute,
  NormalizedAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';

export const hydrator = (denormalizeAttribute: (normalizedAttribute: NormalizedAttribute) => Attribute) => (
  normalizedAttribute: any
): Attribute => {
  const expectedKeys = [
    'identifier',
    'enriched_entity_identifier',
    'code',
    'labels',
    'required',
    'value_per_locale',
    'value_per_channel',
    'type',
  ];

  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');
  normalizedAttribute.identifier.enrichedEntityIdentifier = normalizedAttribute.identifier.enriched_entity_identifier;
  delete normalizedAttribute.identifier.enriched_entity_identifier;
  normalizedAttribute.enrichedEntityIdentifier = normalizedAttribute.enriched_entity_identifier;
  delete normalizedAttribute.enriched_entity_identifier;
  normalizedAttribute.valuePerLocale = normalizedAttribute.value_per_locale;
  delete normalizedAttribute.value_per_locale;
  normalizedAttribute.valuePerChannel = normalizedAttribute.value_per_channel;
  delete normalizedAttribute.value_per_channel;

  return denormalizeAttribute(normalizedAttribute);
};

export default hydrator(denormalizeAttribute);
