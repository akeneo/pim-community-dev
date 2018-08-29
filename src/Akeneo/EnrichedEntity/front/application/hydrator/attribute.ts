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
    'is_required',
    'value_per_locale',
    'value_per_channel',
    'type',
  ];

  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');
  //To remove when backend answer the good things
  normalizedAttribute.is_textarea = normalizedAttribute.is_textarea;

  return denormalizeAttribute(normalizedAttribute);
};

export default hydrator(denormalizeAttribute);
