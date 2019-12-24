import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';

type Value = {
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export const setValueData = <T extends Value = Value>(value: T, data: Data): T => ({...value, data});
export const isValueEmpty = (value: Value): boolean => null === value.data;
export const getValueForChannelAndLocaleFilter = (channel: ChannelReference, locale: LocaleReference) => (
  value: Value
): boolean =>
  (channelReferenceIsEmpty(value.channel) || channelReferenceAreEqual(value.channel, channel)) &&
  (localeReferenceIsEmpty(value.locale) || localeReferenceAreEqual(value.locale, locale));
export const getValuesForChannelAndLocale = <T extends Value = Value>(
  values: T[],
  channel: ChannelReference,
  locale: LocaleReference
): T[] => values.filter(getValueForChannelAndLocaleFilter(channel, locale));

export default Value;
