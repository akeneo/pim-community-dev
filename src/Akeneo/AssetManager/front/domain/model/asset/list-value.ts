import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {MediaPreviewType, MediaPreview} from 'akeneoassetmanager/tools/media-url-generator';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';

type ListValue = {
  attribute: AttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export default ListValue;

export const setValueData = (value: ListValue, data: Data): ListValue => ({...value, data});
export const isValueEmpty = (value: ListValue): boolean => null === value.data;
export const areValuesEqual = (first: ListValue, second: ListValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute === second.attribute;
export const normalizeValue = (value: ListValue) => value;

//TODO factorize with getEditionValueUrl?
export const getListValueMediaPreview = (
  type: MediaPreviewType,
  value: ListValue,
  attributeIdentifier: AttributeIdentifier
): MediaPreview => {
  const data =
    undefined === value || null === value.data
      ? ''
      : isMediaFileData(value.data)
      ? value.data.filePath
      : isMediaLinkData(value.data)
      ? value.data
      : '';

  return {
    type,
    attributeIdentifier,
    data,
  };
};
