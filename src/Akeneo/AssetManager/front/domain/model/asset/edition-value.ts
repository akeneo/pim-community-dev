import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MediaPreviewType, MediaPreview} from 'akeneoassetmanager/tools/media-url-generator';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {isMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import attribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';

type EditionValue = {
  attribute: NormalizedAttribute;
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export default EditionValue;

export const setValueData = (value: EditionValue, data: Data): EditionValue => ({...value, data});
export const isValueEmpty = (value: EditionValue): boolean => null === value.data;
export const isValueComplete = (value: EditionValue): boolean => value.attribute.is_required && !isValueEmpty(value);
export const isValueRequired = (value: EditionValue): boolean => value.attribute.is_required;
export const areValuesEqual = (first: EditionValue, second: EditionValue): boolean =>
  channelReferenceAreEqual(first.channel, second.channel) &&
  localeReferenceAreEqual(first.locale, second.locale) &&
  first.attribute.identifier === second.attribute.identifier;
export const normalizeValue = (value: EditionValue) => value;

export const getData = attribute;

export const getEditionValueMediaPreview = (
  type: MediaPreviewType,
  value: EditionValue,
  attributeIdentifier: AttributeIdentifier
): MediaPreview => {
  const data =
    undefined === value || null === value.data
      ? ''
      : isMediaFileAttribute(value.attribute) && isMediaFileData(value.data)
      ? value.data.filePath
      : isMediaLinkAttribute(value.attribute) && isMediaLinkData(value.data)
      ? value.data
      : '';

  return {
    type,
    attributeIdentifier,
    data,
  };
};
