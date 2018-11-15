import {getAttributeDenormalizer, Denormalizer} from 'akeneoreferenceentity/application/configuration/attribute';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export const denormalizeAttribute = (
  getAttributeDenormalizer: (normalizedAttribute: NormalizedAttribute) => Denormalizer
) => (normalizedAttribute: NormalizedAttribute) => {
  const denormalizer = getAttributeDenormalizer(normalizedAttribute);

  return denormalizer(normalizedAttribute);
};

export default (normalizedAttribute: NormalizedAttribute) =>
  denormalizeAttribute(getAttributeDenormalizer)(normalizedAttribute);
