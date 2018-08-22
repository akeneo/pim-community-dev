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
  normalizedAttribute.max_length = null;
  normalizedAttribute.is_textarea = false;
  normalizedAttribute.is_rich_text_editor = false;
  normalizedAttribute.validation_rule = 'none';
  normalizedAttribute.regular_expression = null;

  return denormalizeAttribute(normalizedAttribute);
};

export default hydrator(denormalizeAttribute);
