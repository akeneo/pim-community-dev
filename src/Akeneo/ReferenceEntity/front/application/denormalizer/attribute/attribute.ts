import {getAttributeDenormalizer} from 'akeneoreferenceentity/application/configuration/attribute';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

const denormalizeAttribute = (normalizedAttribute: NormalizedAttribute) => {
  const denormalizer = getAttributeDenormalizer(normalizedAttribute);

  return denormalizer(normalizedAttribute);
};

export default denormalizeAttribute;
