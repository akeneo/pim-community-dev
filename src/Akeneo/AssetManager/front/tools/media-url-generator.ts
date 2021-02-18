import {File, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
import {MediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';
const routing = require('routing');

export const canCopyToClipboard = (): boolean => 'clipboard' in navigator;

export const copyToClipboard = (text: string) => canCopyToClipboard() && navigator.clipboard.writeText(text);

export const getImageDownloadUrl = (image: File): string => {
  const path = !isFileEmpty(image) ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return routing.generate('pim_enrich_media_download', {filename});
};

export const getMediaPreviewUrl = (mediaPreview: MediaPreview): string =>
  routing.generate('akeneo_asset_manager_image_preview', {
    ...mediaPreview,
    data: btoa(mediaPreview.data),
  });

export const getAssetEditUrl = (asset: ListAsset): string =>
  `#${routing.generate('akeneo_asset_manager_asset_edit', {
    assetFamilyIdentifier: asset.assetFamilyIdentifier,
    assetCode: asset.code,
    tab: 'enrich',
  })}`;

export const getProductEditUrl = (type: string, id: string) => `#${routing.generate(`pim_enrich_${type}_edit`, {id})}`;
