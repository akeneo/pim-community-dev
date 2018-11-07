import {NormalizedAttribute, Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';
import denormalize from 'akeneoreferenceentity/application/denormalizer/attribute/attribute';

export const hydrator = (denormalize: (normalizedAttribute: NormalizedAttribute) => Attribute) => (
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

  return denormalize(normalizedAttribute);
};

const hydrateAttribute = hydrator(denormalize);

export default hydrateAttribute;
