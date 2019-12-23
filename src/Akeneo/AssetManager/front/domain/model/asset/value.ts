import ChannelReference, {channelReferenceIsEmpty, channelReferenceAreEqual} from '../channel-reference';
import LocaleReference, {localeReferenceIsEmpty, localeReferenceAreEqual} from '../locale-reference';
import Data from './data';

type Value = {
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export const setValueData = <T extends Value = Value>(value: T, data: Data): T => ({...value, data});
export const isValueEmpty = (value: Value): boolean => null === value.data;
export const normalizeValue = (value: Value) => value;
export const getValueForChannelAndLocaleFilter = (channel: ChannelReference, locale: LocaleReference) => (
  value: Value
): boolean =>
  (channelReferenceIsEmpty(value.channel) || channelReferenceAreEqual(value.channel, channel)) &&
  (localeReferenceIsEmpty(value.locale) || localeReferenceAreEqual(value.locale, locale));
export const getValuesForChannelAndLocale = <T extends Value = Value>(
  values: T[],
  channel: ChannelReference,
  locale: LocaleReference
) => values.filter(getValueForChannelAndLocaleFilter(channel, locale));

export default Value;
