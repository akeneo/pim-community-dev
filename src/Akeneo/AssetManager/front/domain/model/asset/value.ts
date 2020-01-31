import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceAreEqual,
} from 'akeneoassetmanager/domain/model/locale-reference';
import Data from 'akeneoassetmanager/domain/model/asset/data';
import EditionValue, {isEditionValue} from 'akeneoassetmanager/domain/model/asset/edition-value';
import MediaLinkData from 'akeneoassetmanager/domain/model/asset/data/media-link';
import ListValue from 'akeneoassetmanager/domain/model/asset/list-value';
import MediaFileData from 'akeneoassetmanager/domain/model/asset/data/media-file';

type Value = {
  channel: ChannelReference;
  locale: LocaleReference;
  data: Data;
};

export type PreviewCollection = PreviewModel[];

export type PreviewModel = ListValue & {
  data: MediaFileData | MediaLinkData;
};

export const setValueData = <T extends Value = Value>(value: T, data: Data): T => ({...value, data});
export const isValueEmpty = (value: Value): boolean => null === value.data;
export const getValueForChannelAndLocaleFilter = (channel: ChannelReference, locale: LocaleReference) => (
  value: Value
): boolean =>
  (channelReferenceIsEmpty(value.channel) || channelReferenceAreEqual(value.channel, channel)) &&
  (localeReferenceIsEmpty(value.locale) || localeReferenceAreEqual(value.locale, locale));

export const getValuesForChannelAndLocale = <T extends Value = Value>(
  values: T[],
  channel: ChannelReference,
  locale: LocaleReference
): T[] => values.filter(getValueForChannelAndLocaleFilter(channel, locale));

export const getPreviewModelFromCollection = (
  previews: PreviewCollection,
  channel: ChannelReference,
  locale: LocaleReference
): PreviewModel | undefined => previews.find(getValueForChannelAndLocaleFilter(channel, locale));

export const getPreviewModelFromValue = (
  value: EditionValue | ListValue,
  channel: ChannelReference,
  locale: LocaleReference
): PreviewModel => ({
  data: value.data as MediaLinkData | MediaFileData,
  channel,
  locale,
  attribute: isEditionValue(value) ? value.attribute.identifier : value.attribute,
});

export const isPreviewModelUndefined = (previewModel: any): previewModel is undefined => previewModel === undefined;
export const isPreviewModelNull = (previewModel: any): previewModel is null => previewModel === null;

export default Value;
