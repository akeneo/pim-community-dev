import {Router} from '@akeneo-pim-community/shared';
import {File, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
import {MediaPreview} from 'akeneoassetmanager/domain/model/asset/media-preview';

const canCopyToClipboard = (): boolean => 'clipboard' in navigator;

const copyToClipboard = (text: string) => canCopyToClipboard() && navigator.clipboard.writeText(text);

const getImageDownloadUrl = (router: Router, image: File): string => {
  const path = !isFileEmpty(image) ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return router.generate('pim_enrich_media_download', {filename});
};

const getMediaPreviewUrl = (router: Router, mediaPreview: MediaPreview): string => {
  const isUrlEncoded = mediaPreview.data !== decodeURIComponent(mediaPreview.data);

  return router.generate('akeneo_asset_manager_image_preview', {
    ...mediaPreview,
    data: btoa(isUrlEncoded ? mediaPreview.data : encodeURI(mediaPreview.data)),
  });
};

export {canCopyToClipboard, copyToClipboard, getImageDownloadUrl, getMediaPreviewUrl};
