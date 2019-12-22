import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

type ListValue = {
  attribute: AttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export const setValueData = (value: ListValue, data: Data): ListValue => ({...value, data});
export const isValueEmpty = (value: ListValue): boolean => null === value.data;
export const areValuesEqual = (first: ListValue, second: ListValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute === second.attribute;
export const normalizeValue = (value: ListValue) => value;

export default ListValue;
