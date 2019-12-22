import {File, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {getValueForChannelAndLocaleFilter} from 'akeneoassetmanager/domain/model/asset/value-collection';
const routing = require('routing');

//TODO is this the right place?
export enum MediaPreviewType {
  Preview = 'preview',
  Thumbnail = 'thumbnail',
  ThumbnailSmall = 'thumbnail_small',
}

// TODO move this to right place
export type MediaPreview = {
  type: MediaPreviewType;
  attributeIdentifier: AttributeIdentifier;
  data: string;
};

export const canCopyToClipboard = (): boolean => 'clipboard' in navigator;

export const copyToClipboard = (text: string) => canCopyToClipboard() && navigator.clipboard.writeText(text);

export const getImageShowUrl = (image: File, filter: string): string => {
  const path = !isFileEmpty(image) ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return routing.generate('pim_enrich_media_show', {filename, filter});
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

export const getMediaPreviewUrl = (mediaPreview: MediaPreview): string =>
  routing.generate('akeneo_asset_manager_image_preview', {...mediaPreview, data: btoa(mediaPreview.data)});

// TODO The asset any is temporary and should be fixed when we create unified models
export const getAssetPreview = (asset: any, type: MediaPreviewType, {locale, channel}: Context): string => {
  const image = asset.image.find(getValueForChannelAndLocaleFilter(channel, locale));

  //TODO unify models https://akeneo.atlassian.net/browse/AST-183
  const attributeIdentifier =
    undefined !== asset.assetFamily
      ? asset.assetFamily.attributeAsMainMedia
      : 0 === asset.image.length
      ? 'UNKNOWN'
      : asset.image[0].attribute;

  const data = undefined !== image ? (typeof image.data === 'string' ? image.data : image.data.filePath) : '';

  return getMediaPreviewUrl({type, attributeIdentifier, data});
};

// TODO The asset any is temporary and should be fixed when we create unified models
export const getAssetEditUrl = (asset: any): string => {
  const assetFamilyIdentifier = asset.assetFamily.identifier;
  const assetCode = asset.code;

  //TODO cleaner way?
  return '#' + routing.generate('akeneo_asset_manager_asset_edit', {assetFamilyIdentifier, assetCode, tab: 'enrich'});
};
