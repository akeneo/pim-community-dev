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
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreview, MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getValuesForChannelAndLocale} from 'akeneoassetmanager/domain/model/asset/value';

export type EditionValueCollection = EditionValue[];

type EditionAsset = {
  identifier: AssetIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  assetFamily: AssetFamily;
  values: EditionValueCollection;
};

export const createEmptyEditionAsset = (): EditionAsset => ({
  identifier: '',
  code: '',
  labels: {},
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

export const getEditionAssetMainMediaThumbnail = (
  asset: EditionAsset,
  channel: ChannelCode,
  locale: LocaleCode
): MediaPreview => {
  const attributeIdentifier = asset.assetFamily.attributeAsMainMedia;
  const mediaValue = getEditionValue(asset.values, attributeIdentifier, channel, locale);

  return {
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier,
    data: undefined !== mediaValue ? getMediaData(mediaValue.data) : '',
  };
};

export default EditionAsset;
