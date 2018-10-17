import {NormalizedAttribute, Attribute} from 'akeneoreferenceentity/domain/model/attribute/common';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';
import denormalizeAttribute from 'akeneoreferenceentity/application/denormalizer/attribute/attribute';

export const hydrator = (denormalizeAttribute: (normalizedAttribute: NormalizedAttribute) => Attribute) => (
  normalizedAttribute: any
): Attribute => {
  const expectedKeys = [
    'identifier',
    'reference_entity_identifier',
    'code',
    'labels',
    'is_required',
    'value_per_locale',
    'value_per_channel',
    'type',
  ];
  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');

  return denormalizeAttribute(normalizedAttribute);
};

export default hydrator(denormalizeAttribute);
