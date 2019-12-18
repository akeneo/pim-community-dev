import {identifierStringValue} from 'akeneoassetmanager/domain/model/identifier';

const routing = require('routing');
import {File, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
import MediaLinkData, {mediaLinkDataStringValue} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {
  MediaLinkAttribute,
  NormalizedMediaLinkAttribute,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {suffixStringValue} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {prefixStringValue} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {getValueForChannelAndLocaleFilter} from 'akeneoassetmanager/domain/model/asset/value-collection';

export enum MediaPreviewTypes {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
  ThumbnailSmall = 'thumbnail_small',
}

export const canCopyToClipboard = (): boolean => 'clipboard' in navigator;

export const copyToClipboard = (text: string) => canCopyToClipboard() && navigator.clipboard.writeText(text);

export const getImageShowUrl = (image: File, filter: string): string => {
  const path = !isFileEmpty(image) ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return routing.generate('pim_enrich_media_show', {filename, filter});
};

export const getFilePreviewUrl = (type: string, file: File, attributeIdentifier: AttributeIdentifier): string => {
  if (file === null) return getDefaultImagePreviewUrl(type, attributeIdentifier);

  const stringIdentifier = identifierStringValue(attributeIdentifier);

  const data = btoa(file.filePath);
  return routing.generate('akeneo_asset_manager_image_preview', {type, attributeIdentifier: stringIdentifier, data});
};

const getDefaultImagePreviewUrl = (type: string, attributeIdentifier: AttributeIdentifier): string => {
  const stringIdentifier = identifierStringValue(attributeIdentifier);
  return routing.generate('akeneo_asset_manager_image_preview', {
    type,
    attributeIdentifier: stringIdentifier,
    data: '',
  });
};

export const getImageDownloadUrl = (image: File): string => {
  const path = !isFileEmpty(image) ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return routing.generate('pim_enrich_media_download', {filename});
};

/**
 * Get the show media URL
 *
 * @param string filePath
 * @param string filter
 *
 * @return {string}
 */
export const getMediaShowUrl = (filePath: string, filter: string): string => {
  const filename = encodeURIComponent(filePath);

  return routing.generate('pim_enrich_media_show', {filename, filter});
};

/**
 * Get the download media URL
 *
 * @param string filePath
 *
 * @return {string}
 */
export const getMediaDownloadUrl = (filePath: string): string => {
  const filename = encodeURIComponent(filePath);

  return routing.generate('pim_enrich_media_download', {filename});
};

export const getMediaLinkPreviewUrl = (
  type: string,
  mediaLink: MediaLinkData,
  // TODO: use the attribute light model but for now I don't want to break the whole world
  // https://akeneo.atlassian.net/browse/AST-183
  attribute: NormalizedMediaLinkAttribute | MediaLinkAttribute
): string => {
  const data = btoa(mediaLinkDataStringValue(mediaLink));
  const attributeIdentifier = attribute.identifier;

  return routing.generate('akeneo_asset_manager_image_preview', {type, attributeIdentifier, data});
};

export const getMediaLinkUrl = (
  mediaLink: MediaLinkData,
  // TODO: use the attribute light model but for now I don't want to break the whole world
  // https://akeneo.atlassian.net/browse/AST-183
  attribute: MediaLinkAttribute | NormalizedMediaLinkAttribute
): string => {
  return `${prefixStringValue(attribute.prefix)}${mediaLinkDataStringValue(mediaLink)}${suffixStringValue(
    attribute.suffix
  )}`;
};

// The asset any is temporary and should be fixed when we create unified models
export const getAssetPreview = (asset: any, type: MediaPreviewTypes, {locale, channel}: Context): string => {
  const image = asset.image.find(getValueForChannelAndLocaleFilter(channel, locale));

  //TODO unify models https://akeneo.atlassian.net/browse/AST-183
  const attributeIdentifier =
    undefined !== asset.assetFamily
      ? asset.assetFamily.attributeAsMainMedia
      : 0 === asset.image.length
      ? 'UNKNOWN'
      : asset.image[0].attribute;

  const data = undefined !== image ? btoa(typeof image.data === 'string' ? image.data : image.data.filePath) : '';

  return routing.generate('akeneo_asset_manager_image_preview', {
    type,
    attributeIdentifier,
    data,
  });
};

// The asset any is temporary and should be fixed when we create unified models
// https://akeneo.atlassian.net/browse/AST-183
export const getAssetEditUrl = (asset: any): string => {
  const assetFamilyIdentifier = asset.assetFamily.identifier;
  const assetCode = asset.code;

  //TODO cleaner way?
  return '#' + routing.generate('akeneo_asset_manager_asset_edit', {assetFamilyIdentifier, assetCode, tab: 'enrich'});
};

export const getFileThumbnailUrl = (attributeIdentifier: AttributeIdentifier, file: File) => {
  return routing.generate('akeneo_asset_manager_image_preview', {
    type: MediaPreviewTypes.Thumbnail,
    attributeIdentifier,
    data: null !== file ? btoa(file.filePath) : '',
  });
};
