import {File, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
import {MediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
const routing = require('routing');

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

export const getMediaPreviewUrl = (mediaPreview: MediaPreview): string =>
  routing.generate('akeneo_asset_manager_image_preview', {
    ...mediaPreview,
    data: btoa(mediaPreview.data),
  });

export const getAssetEditUrl = (asset: ListAsset): string => {
  const assetFamilyIdentifier = asset.assetFamilyIdentifier;
  const assetCode = asset.code;

  //TODO cleaner way?
  return (
    '#' +
    routing.generate('akeneo_asset_manager_asset_edit', {
      assetFamilyIdentifier,
      assetCode,
      tab: 'enrich',
    })
  );
};
