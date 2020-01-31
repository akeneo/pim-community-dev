import {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import Value from 'akeneoassetmanager/domain/model/asset/value';

type ListValue = Value & {
  attribute: AttributeIdentifier;
};

export const areValuesEqual = (first: ListValue, second: ListValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute === second.attribute;

export default ListValue;
