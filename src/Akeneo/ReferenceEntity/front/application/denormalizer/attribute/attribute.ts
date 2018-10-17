import {getAttributeDenormalizer} from 'akeneoreferenceentity/application/configuration/attribute';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/common';

const denormalizeAttribute = (normalizedAttribute: NormalizedAttribute) => {
  const denormalizer = getAttributeDenormalizer(normalizedAttribute);

  return denormalizer(normalizedAttribute);
};

export default denormalizeAttribute;
