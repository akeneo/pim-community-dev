const routing = require('routing');
import File, {FilePath} from 'akeneoreferenceentity/domain/model/file';

const isAssetManagerImagePath = (path: FilePath): boolean => path.includes('rest/asset_manager/image_preview');

export const getImageShowUrl = (image: File, filter: string): string => {
  const path = !image.isEmpty() ? image.getFilePath() : 'undefined';
  if (isAssetManagerImagePath(path)) {
    return path;
  }

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
