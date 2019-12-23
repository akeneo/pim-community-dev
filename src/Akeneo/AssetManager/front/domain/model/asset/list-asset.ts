import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier from 'akeneoassetmanager/domain/model/asset/identifier';
import ListValue, {getPreviewModel} from 'akeneoassetmanager/domain/model/asset/list-value';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import ChannelReference, {
  channelReferenceIsEmpty,
  channelReferenceStringValue,
} from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference, {
  localeReferenceIsEmpty,
  localeReferenceStringValue,
} from 'akeneoassetmanager/domain/model/locale-reference';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {MediaPreview, MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import MediaFileData from 'akeneoassetmanager/domain/model/asset/data/media-file';
import MediaLinkData from 'akeneoassetmanager/domain/model/asset/data/media-link';

export type ValueCollection = {[key: string]: ListValue};

export type PreviewCollection = PreviewModel[];

export type PreviewModel = ListValue & {
  data: MediaFileData | MediaLinkData;
};

//TODO refactor image naming
type ListAsset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  image: PreviewCollection;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  values: ValueCollection;
  completeness: NormalizedCompleteness;
};

export const generateKey = (
  attributeIdentifier: AttributeIdentifier,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  let key = attributeIdentifierStringValue(attributeIdentifier);
  key = !channelReferenceIsEmpty(channel) ? `${key}_${channelReferenceStringValue(channel)}` : key;
  key = !localeReferenceIsEmpty(locale) ? `${key}_${localeReferenceStringValue(locale)}` : key;

  return key;
};

export const generateValueKey = (value: ListValue) => generateKey(value.attribute, value.channel, value.locale);

export const getListAssetMainMediaPreview = (
  asset: ListAsset,
  attributeAsMainMedia: NormalizedAttribute,
  channel: ChannelCode,
  locale: LocaleCode
): MediaPreview => {
  const attributeIdentifier = attributeAsMainMedia.identifier;
  const previewModel = getPreviewModel(asset.image, channel, locale);

  return {
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier,
    data: undefined !== previewModel ? getMediaData(previewModel.data, attributeAsMainMedia) : '',
  };
};

export const isMainMediaEmpty = (asset: ListAsset, channel: ChannelCode, locale: LocaleCode) => {
  const previewModel = getPreviewModel(asset.image, channel, locale);
  return undefined === previewModel || null === previewModel.data;
};

export default ListAsset;
