import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

type Value = {
  attribute: NormalizedAttribute;
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export default Value;

export const setValueData = (value: Value, data: Data): Value => ({...value, data});

export const isValueEmpty = (value: Value): boolean => null === value.data;
export const isValueComplete = (value: Value): boolean => value.attribute.is_required && !isValueEmpty(value);

export const isValueRequired = (value: Value): boolean => value.attribute.is_required;

export const areValueEqual = (first: Value, second: Value): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute.identifier === second.attribute.identifier;

export const normalizeValue = (value: Value) => value;
