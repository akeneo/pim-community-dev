import Value, {createValue, NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalizeChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {
  getDataDenormalizer,
  Denormalizer as DataDenormalizer,
} from 'akeneoreferenceentity/application/configuration/value';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {
  Denormalizer as AttributeDenormalizer,
  getAttributeDenormalizer,
} from 'akeneoreferenceentity/application/configuration/attribute';

export const getValueDenormalizer = (
  getDataDenormalizer: (normalizedValue: NormalizedValue) => DataDenormalizer,
  getAttributeDenormalizer: (normalizedAttribute: NormalizedAttribute) => AttributeDenormalizer
) => (normalizedValue: NormalizedValue): Value => {
  const denormalizeAttribute = getAttributeDenormalizer(normalizedValue.attribute);

  const denormalizeData = getDataDenormalizer(normalizedValue);

  return createValue(
    denormalizeAttribute(normalizedValue.attribute),
    denormalizeChannelReference(normalizedValue.channel),
    denormalizeLocaleReference(normalizedValue.locale),
    denormalizeData(normalizedValue.data)
  );
};

export default getValueDenormalizer(getDataDenormalizer, getAttributeDenormalizer);
