import Value, {createValue, NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalizeChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {denormalizeAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {getDataDenormalizer, Denormalizer} from 'akeneoreferenceentity/application/configuration/value';

export const getValueDenormalizer = (getDataDenormalizer: (normalizedValue: NormalizedValue) => Denormalizer) => (
  normalizedValue: NormalizedValue
): Value => {
  const attribute = denormalizeAttribute(normalizedValue.attribute);

  const denormalizeData = getDataDenormalizer(normalizedValue);

  return createValue(
    attribute,
    denormalizeChannelReference(normalizedValue.channel),
    denormalizeLocaleReference(normalizedValue.locale),
    denormalizeData(normalizedValue.data)
  );
};

export default getValueDenormalizer(getDataDenormalizer);
