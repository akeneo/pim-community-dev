import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import Value, {getValueForChannelAndLocaleFilter} from 'akeneoassetmanager/domain/model/asset/value';

type ListValue = Value & {
  attribute: AttributeIdentifier;
};

export type ListValueCollection = ListValue[];

export const areValuesEqual = (first: ListValue, second: ListValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute === second.attribute;

export const getListValue = (
  values: ListValueCollection,
  channel: ChannelReference,
  locale: LocaleReference
): ListValue | undefined => values.find(getValueForChannelAndLocaleFilter(channel, locale));

export default ListValue;
