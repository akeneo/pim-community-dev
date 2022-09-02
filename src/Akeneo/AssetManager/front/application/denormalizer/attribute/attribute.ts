import {Denormalizer} from 'akeneoassetmanager/application/configuration/attribute';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

const denormalizeAttribute = (getAttributeDenormalizer: (normalizedAttribute: NormalizedAttribute) => Denormalizer) => (
  normalizedAttribute: NormalizedAttribute
) => {
  const denormalizer = getAttributeDenormalizer(normalizedAttribute);

  return denormalizer(normalizedAttribute);
};

export {denormalizeAttribute};
