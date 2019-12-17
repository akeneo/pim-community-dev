import Value from 'akeneoassetmanager/domain/model/asset/value';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';

type BackendValue = Value; //TODO create a real backend model

export const valueDenormalizer = (normalizedValue: BackendValue): Value => {
  return {
    attribute: normalizedValue.attribute,
    channel: denormalizeChannelReference(normalizedValue.channel),
    locale: denormalizeLocaleReference(normalizedValue.locale),
    data: normalizedValue.data,
  };
};

export default valueDenormalizer;
