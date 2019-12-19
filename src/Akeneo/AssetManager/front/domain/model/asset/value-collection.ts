import Value from 'akeneoassetmanager/domain/model/asset/value';
import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceAreEqual,
  channelReferenceStringValue,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceAreEqual,
  localeReferenceStringValue,
} from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';

export const getValueForChannelAndLocaleFilter = (channel: ChannelReference, locale: LocaleReference) => (
  value: Value
): boolean =>
  (channelReferenceIsEmpty(value.channel) || channelReferenceAreEqual(value.channel, channel)) &&
  (localeReferenceIsEmpty(value.locale) || localeReferenceAreEqual(value.locale, locale));

export const getValueForAttributeIdentifierFilter = (attributeIdentifier: AttributeIdentifier) => (value: Value) =>
  value.attribute.identifier === attributeIdentifier;

export const getValueFilter = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  const attributeFilter = getValueForAttributeIdentifierFilter(attributeIdentifier);
  const channelAndLocaleFilter = getValueForChannelAndLocaleFilter(channel, locale);

  return (value: Value) => attributeFilter(value) && channelAndLocaleFilter(value);
};

type ValueCollection = Value[];

export const getValuesForChannelAndLocale = (
  values: ValueCollection,
  channel: ChannelReference,
  locale: LocaleReference
) => values.filter(getValueForChannelAndLocaleFilter(channel, locale));

export default ValueCollection;

export const generateKey = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  let key = attributeIdentifierStringValue(attributeIdentifier);
  key = !channelReferenceIsEmpty(channel) ? `${key}_${channelReferenceStringValue(channel)}` : key;
  key = !localeReferenceIsEmpty(locale) ? `${key}_${localeReferenceStringValue(locale)}` : key;

  return key;
};
