import Value, {createValue, NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import {
  getDataDenormalizer,
  Denormalizer as DataDenormalizer,
} from 'akeneoassetmanager/application/configuration/value';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  Denormalizer as AttributeDenormalizer,
  getAttributeDenormalizer,
} from 'akeneoassetmanager/application/configuration/attribute';

export const getValueDenormalizer = (
  getDataDenormalizer: (normalizedValue: NormalizedValue) => DataDenormalizer,
  getAttributeDenormalizer: (normalizedAttribute: NormalizedAttribute) => AttributeDenormalizer
) => (normalizedValue: NormalizedValue): Value => {
  const denormalizeAttribute = getAttributeDenormalizer(normalizedValue.attribute);
  const denormalizedAttribute = denormalizeAttribute(normalizedValue.attribute);

  const denormalizeData = getDataDenormalizer(normalizedValue);
  return createValue(
    denormalizedAttribute,
    denormalizeChannelReference(normalizedValue.channel),
    denormalizeLocaleReference(normalizedValue.locale),
    denormalizeData(normalizedValue.data, denormalizedAttribute)
  );
};

export default (normalizedValue: NormalizedValue): Value =>
  getValueDenormalizer(getDataDenormalizer, getAttributeDenormalizer)(normalizedValue);
