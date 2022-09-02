import {useAttributeConfig} from '../useAttributeConfig';
import {getDenormalizer} from '../../configuration/attribute';
import {NormalizedAttribute} from '../../../domain/model/attribute/attribute';

const useAttributeDenormalizer = () => {
  const attributeConfig = useAttributeConfig();

  return (normalizedAttribute: NormalizedAttribute) => {
    const denormalizer = getDenormalizer(attributeConfig, normalizedAttribute);

    return denormalizer(normalizedAttribute);
  };
};

export {useAttributeDenormalizer};
