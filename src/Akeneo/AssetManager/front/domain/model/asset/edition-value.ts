import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

type EditionValue = {
  attribute: NormalizedAttribute;
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export const setValueData = (value: EditionValue, data: Data): EditionValue => ({...value, data});
export const isValueEmpty = (value: EditionValue): boolean => null === value.data;
export const isValueComplete = (value: EditionValue): boolean => value.attribute.is_required && !isValueEmpty(value);
export const isValueRequired = (value: EditionValue): boolean => value.attribute.is_required;
export const areValuesEqual = (first: EditionValue, second: EditionValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute.identifier === second.attribute.identifier;
export const normalizeValue = (value: EditionValue) => value;

export default EditionValue;
