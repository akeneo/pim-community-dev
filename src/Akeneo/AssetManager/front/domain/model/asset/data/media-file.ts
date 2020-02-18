import {File, areFilesEqual, isFile, isFileEmpty} from 'akeneoassetmanager/domain/model/file';

export const PLACEHOLDER_PATH = '/bundles/pimui/img/image_default.png';

type MediaFileData = File;
export type NormalizedMediaFileData = File;

export const areMediaFileDataEqual = areFilesEqual;

export const isMediaFileData = (mediaFileData: any): mediaFileData is MediaFileData => isFile(mediaFileData);

export const getMediaFilePath = (mediaFile: File) => (isFileEmpty(mediaFile) ? PLACEHOLDER_PATH : mediaFile.filePath);

export default MediaFileData;
