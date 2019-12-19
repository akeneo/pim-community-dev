import {File, areFilesEqual, isFile} from 'akeneoassetmanager/domain/model/file';

type MediaFileData = File;
export type NormalizedMediaFileData = File;

export default MediaFileData;
export const areMediaFileDataEqual = areFilesEqual;
export const isMediaFileData = (mediaFileData: any): mediaFileData is MediaFileData => isFile(mediaFileData);
