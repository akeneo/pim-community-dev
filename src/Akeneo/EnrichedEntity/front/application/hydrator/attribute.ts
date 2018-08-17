import Attribute, {
  denormalizeAttribute,
  NormalizedAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';

const humps = require('humps');

export const hydrator = (denormalizeAttribute: (normalizedAttribute: NormalizedAttribute) => Attribute) => (
  normalizedAttribute: any
): Attribute => {
  let labels = {};
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

  ({labels, ...normalizedAttribute} = normalizedAttribute);
  const formattedAttribute = {...humps.camelizeKeys(normalizedAttribute), labels};

  return denormalizeAttribute(formattedAttribute);
};

export default hydrator(denormalizeAttribute);
