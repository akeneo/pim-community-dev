const routing = require('routing');
import {ImageInterface} from 'pimfront/product/domain/model/product';

export const getImageShowUrl = (image: ImageInterface | null, filter: string): string => {
  const path = null !== image ? image.filePath : 'undefined';
  const filename = encodeURIComponent(path);

  return routing.generate('pim_enrich_media_show', {filename, filter});
};

export const getImageDownloadUrl = (image: ImageInterface | null): string => {
  const path = null !== image ? image.filePath : 'undefined';
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
