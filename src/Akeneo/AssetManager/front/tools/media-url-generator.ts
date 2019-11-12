const routing = require('routing');
import File from 'akeneoassetmanager/domain/model/file';
import MediaLinkData from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {MediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {suffixStringValue} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {prefixStringValue} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';

export enum MediaPreviewTypes {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
  ThumbnailSmall = 'thumbnail_small',
}

export const canCopyToClipboard = (): boolean => 'clipboard' in navigator;

export const copyToClipboard = (text: string) => canCopyToClipboard() && navigator.clipboard.writeText(text);

export const getImageShowUrl = (image: File, filter: string): string => {
  const path = !image.isEmpty() ? image.getFilePath() : 'undefined';
  const filename = encodeURIComponent(path);

  return routing.generate('pim_enrich_media_show', {filename, filter});
};

export const getImageDownloadUrl = (image: File): string => {
  const path = !image.isEmpty() ? image.getFilePath() : 'undefined';
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
  const data = encodeURIComponent(mediaLink.stringValue());
  const attributeIdentifier = attribute.identifier;

  return routing.generate('akeneo_asset_manager_image_preview', {type, attributeIdentifier, data});
};

export const getMediaLinkUrl = (mediaLink: MediaLinkData, attribute: MediaLinkAttribute): string => {
  return prefixStringValue(attribute.prefix) + mediaLink.stringValue() + suffixStringValue(attribute.suffix);
};

// The asset any is temporary and should be fixed when we create unified models
export const getAssetPreview = (asset: any, type: MediaPreviewTypes): string => {
  const image = asset.image[0]; //This should be changed when we will display localisable/scopable images

  if (undefined === image || '' === image.attribute) return '';

  return routing.generate('akeneo_asset_manager_image_preview', {
    type,
    attributeIdentifier: undefined !== image ? image.attribute : '',
    data: undefined !== image ? image.data.filePath : '',
  });
};

// The asset any is temporary and should be fixed when we create unified models
export const getAssetEditUrl = (asset: any): string => {
  const assetFamilyIdentifier = asset.assetFamily.identifier;
  const assetCode = asset.code;

  //TODO cleaner way?
  return '#' + routing.generate('akeneo_asset_manager_asset_edit', {assetFamilyIdentifier, assetCode, tab: 'enrich'});
};
