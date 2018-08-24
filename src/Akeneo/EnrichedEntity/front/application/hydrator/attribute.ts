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

  //To remove when backend answer the good things
  normalizedAttribute.is_required = false;
  validateKeys(normalizedAttribute, expectedKeys, 'The provided raw attribute seems to be malformed.');
  //To remove when backend answer the good things
  normalizedAttribute.allowed_extensions = [];
  normalizedAttribute.max_file_size = null;
  normalizedAttribute.is_textarea = false;
  normalizedAttribute.is_rich_text_editor = false;
  normalizedAttribute.validation_rule = 'none';
  normalizedAttribute.regular_expression = null;

  return denormalizeAttribute(normalizedAttribute);
};

export default hydrator(denormalizeAttribute);
