import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import Value, {isValueEmpty, getValueForChannelAndLocaleFilter} from 'akeneoassetmanager/domain/model/asset/value';
import {EditionValueCollection} from 'akeneoassetmanager/domain/model/asset/edition-asset';

type EditionValue = Value & {
  attribute: NormalizedAttribute;
};

export const isValueComplete = (value: EditionValue): boolean => value.attribute.is_required && !isValueEmpty(value);
export const isValueRequired = (value: EditionValue): boolean => value.attribute.is_required;
export const areValuesEqual = (first: EditionValue, second: EditionValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute.identifier === second.attribute.identifier;

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

export const getEditionValue = (
  values: EditionValueCollection,
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
): EditionValue | undefined => values.find(getValueFilter(attributeIdentifier, channel, locale));

export default EditionValue;
