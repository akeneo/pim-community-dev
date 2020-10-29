import {File, FilePath, areFilesEqual, isFile, isFileEmpty} from 'akeneoassetmanager/domain/model/file';
const routing = require('routing');

const isAssetManagerImagePath = (path: FilePath): boolean => path.includes('rest/asset_manager/image_preview');

export const PLACEHOLDER_PATH = '/bundles/pimui/img/image_default.png';

type MediaFileData = File;
export type NormalizedMediaFileData = File;

export const areMediaFileDataEqual = areFilesEqual;

export const isMediaFileData = (mediaFileData: any): mediaFileData is MediaFileData => isFile(mediaFileData);

export const getMediaFilePath = (mediaFile: File) => {
  if (isFileEmpty(mediaFile)) {
    return PLACEHOLDER_PATH;
  }
  if (isAssetManagerImagePath(mediaFile.filePath)) {
    return mediaFile.filePath;
  }

  return routing.generate('pim_enrich_media_show', {
    filename: encodeURIComponent(mediaFile.filePath),
    filter: 'thumbnail',
  });
};

export default MediaFileData;
