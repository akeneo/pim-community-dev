import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import Value, {getValueForChannelAndLocaleFilter} from 'akeneoassetmanager/domain/model/asset/value';
import {PreviewCollection, PreviewModel} from 'akeneoassetmanager/domain/model/asset/list-asset';

type ListValue = Value & {
  attribute: AttributeIdentifier;
};

export const areValuesEqual = (first: ListValue, second: ListValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute === second.attribute;

// export const getValueForAttributeIdentifierFilter = (attributeIdentifier: AttributeIdentifier) => (value: ListValue) =>
//   value.attribute === attributeIdentifier;

// export const getValueFilter = (
//   attributeIdentifier: AttributeIdentifier,
//   channel: ChannelReference,
//   locale: LocaleReference
// ) => {
//   const attributeFilter = getValueForAttributeIdentifierFilter(attributeIdentifier);
//   const channelAndLocaleFilter = getValueForChannelAndLocaleFilter(channel, locale);

//   return (value: ListValue) => attributeFilter(value) && channelAndLocaleFilter(value);
// };

export const getPreviewModel = (
  previews: PreviewCollection,
  channel: ChannelReference,
  locale: LocaleReference
): PreviewModel | undefined => previews.find(getValueForChannelAndLocaleFilter(channel, locale));

export default ListValue;
