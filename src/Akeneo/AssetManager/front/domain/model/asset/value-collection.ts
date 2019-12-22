import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

export const getValueForChannelAndLocaleFilter = (channel: ChannelReference, locale: LocaleReference) => (
  value: EditionValue
): boolean =>
  (channelReferenceIsEmpty(value.channel) || channelReferenceAreEqual(value.channel, channel)) &&
  (localeReferenceIsEmpty(value.locale) || localeReferenceAreEqual(value.locale, locale));

export const getValueForAttributeIdentifierFilter = (attributeIdentifier: AttributeIdentifier) => (
  value: EditionValue
) => value.attribute.identifier === attributeIdentifier;

export const getValueFilter = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  const attributeFilter = getValueForAttributeIdentifierFilter(attributeIdentifier);
  const channelAndLocaleFilter = getValueForChannelAndLocaleFilter(channel, locale);

  return (value: EditionValue) => attributeFilter(value) && channelAndLocaleFilter(value);
};

type ValueCollection = EditionValue[];

export const getValuesForChannelAndLocale = (
  values: ValueCollection,
  channel: ChannelReference,
  locale: LocaleReference
) => values.filter(getValueForChannelAndLocaleFilter(channel, locale));

//TODO move to EditionAsset
export const getValue = (
  values: ValueCollection,
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
): EditionValue | undefined => values.find(getValueFilter(attributeIdentifier, channel, locale));

export default ValueCollection;
