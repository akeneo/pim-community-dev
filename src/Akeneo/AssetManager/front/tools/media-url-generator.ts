import {identifierStringValue} from 'akeneoassetmanager/domain/model/identifier';

const routing = require('routing');
import {File, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
import MediaLinkData from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {MediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
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

// TODO remove this comment when using typescript ^3.4
// @ts-ignore eslint-disable-next-line flowtype/no-flow-fix-me-comments
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
  attribute: MediaLinkAttribute
): string => {
  const data = btoa(mediaLink.stringValue());
  const attributeIdentifier = attribute.identifier;

  return routing.generate('akeneo_asset_manager_image_preview', {type, attributeIdentifier, data});
};

export const getMediaLinkUrl = (mediaLink: MediaLinkData, attribute: MediaLinkAttribute): string => {
  return prefixStringValue(attribute.prefix) + mediaLink.stringValue() + suffixStringValue(attribute.suffix);
};

// The asset any is temporary and should be fixed when we create unified models
export const getAssetPreview = (asset: any, type: MediaPreviewTypes, {locale, channel}: Context): string => {
  const image = asset.image.find(getValueForChannelAndLocaleFilter(channel, locale));

  if (undefined === image || '' === image.attribute) return '';

  return routing.generate('akeneo_asset_manager_image_preview', {
    type,
    attributeIdentifier: undefined !== image ? image.attribute : '',
    data: undefined !== image ? btoa(image.data.filePath) : '',
  });
};

// The asset any is temporary and should be fixed when we create unified models
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
