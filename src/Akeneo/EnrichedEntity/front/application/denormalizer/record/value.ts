import Value, {createValue, NormalizedValue} from 'akeneoenrichedentity/domain/model/record/value';
import {denormalizeChannelReference} from 'akeneoenrichedentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoenrichedentity/domain/model/locale-reference';
import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {getDataDenormalizer} from 'akeneoenrichedentity/application/configuration/data';

const denormalizeValue = (normalizedValue: NormalizedValue): Value => {
  const attribute = denormalizeAttribute(normalizedValue.attribute);

  const denormalizeData = getDataDenormalizer(normalizedValue);

  return createValue(
    attribute,
    denormalizeChannelReference(normalizedValue.channel),
    denormalizeLocaleReference(normalizedValue.locale),
    denormalizeData(normalizedValue.data)
  );
};

export default denormalizeValue;
