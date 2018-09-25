import Value, {createValue, NormalizedValue} from 'akeneoenrichedentity/domain/model/record/value';
import {denormalizeChannelReference} from 'akeneoenrichedentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoenrichedentity/domain/model/locale-reference';
import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {getDataDenormalizer, Denormalizer} from 'akeneoenrichedentity/application/configuration/value';

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
