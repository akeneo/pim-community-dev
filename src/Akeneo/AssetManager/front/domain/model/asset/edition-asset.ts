import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetIdentifier from 'akeneoassetmanager/domain/model/asset/identifier';
import EditionValue, {getEditionValue} from 'akeneoassetmanager/domain/model/asset/edition-value';
import {AssetFamily, createEmptyAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import {getMediaData, MediaData, isDataEmpty} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreview, MediaPreviewType, emptyMediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getValuesForChannelAndLocale} from 'akeneoassetmanager/domain/model/asset/value';

export type EditionValueCollection = EditionValue[];

type EditionAsset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  createdAt: string;
  updatedAt: string;
  assetFamily: AssetFamily;
  values: EditionValueCollection;
};

export const createEmptyEditionAsset = (): EditionAsset => ({
  identifier: '',
  code: '',
  labels: {},
  createdAt: '',
  updatedAt: '',
  assetFamily: createEmptyAssetFamily(),
  values: [],
});

export const getEditionAssetCompleteness = (
  asset: EditionAsset,
  channel: ChannelReference,
  locale: LocaleReference
): Completeness => {
  // TODO use completeness light model
  const values = getValuesForChannelAndLocale(asset.values, channel, locale);

  return Completeness.createFromValues(values);
};

export const getEditionAssetLabel = (editionAsset: EditionAsset, locale: LocaleCode): string =>
  getLabel(editionAsset.labels, locale, editionAsset.code);

export const getEditionAssetMediaData = (asset: EditionAsset, channel: ChannelCode, locale: LocaleCode): MediaData => {
  const value = getEditionValue(asset.values, asset.assetFamily.attributeAsMainMedia, channel, locale);

  return value ? (value.data as MediaData) : null;
};

export const getEditionAssetMainMediaThumbnail = (
  asset: EditionAsset,
  channel: ChannelCode,
  locale: LocaleCode,
  previewType: MediaPreviewType = MediaPreviewType.Thumbnail
): MediaPreview => {
  const mediaData = getEditionAssetMediaData(asset, channel, locale);
  if (isDataEmpty(mediaData)) {
    return emptyMediaPreview();
  }

  return {
    type: previewType,
    attributeIdentifier: asset.assetFamily.attributeAsMainMedia,
    data: getMediaData(mediaData),
  };
};

export default EditionAsset;
