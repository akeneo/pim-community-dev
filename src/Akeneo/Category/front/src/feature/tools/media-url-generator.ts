import {Router} from '@akeneo-pim-community/shared';
import {File, isFileEmpty} from '../models/File';
import {MediaPreview} from '../models/MediaPreview';

const canCopyToClipboard = (): boolean => 'clipboard' in navigator;

const copyToClipboard = (text: string) => canCopyToClipboard() && navigator.clipboard.writeText(text);

const getImageDownloadUrl = (router: Router, image: File): string => {
  const path = !isFileEmpty(image) ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return router.generate('pim_enrich_media_download', {filename});
};

const getMediaPreviewUrl = (router: Router, mediaPreview: MediaPreview): string => {
  let isUrlEncoded = false;
  try {
    isUrlEncoded = mediaPreview.data !== decodeURIComponent(mediaPreview.data);
  } catch (error) {
    if (!(error instanceof URIError)) {
      throw error;
    }
  }

  return router.generate('pim_enriched_category_rest_image_preview', {
    ...mediaPreview,
    data: btoa(isUrlEncoded ? mediaPreview.data : encodeURI(mediaPreview.data)),
  });
};

export {canCopyToClipboard, copyToClipboard, getImageDownloadUrl, getMediaPreviewUrl};
