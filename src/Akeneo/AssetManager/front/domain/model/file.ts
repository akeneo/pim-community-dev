import {FileInfo} from 'akeneo-design-system';

export type FilePath = string;
export type File = FileInfo | null;

export const createEmptyFile = (): File => null;
export const createFileFromNormalized = (file: any): File => file;
export const isFileEmpty = (file: File): file is null => null === file;
export const areFilesEqual = (first: File, second: File) =>
  (null === first && null === second) ||
  (null !== first &&
    null !== second &&
    first.filePath === second.filePath &&
    first.originalFilename === second.originalFilename &&
    first.size === second.size &&
    first.mimeType === second.mimeType &&
    first.extension === second.extension);

export const isFileInStorage = (file: File) => !isFileEmpty(file) && -1 === file.filePath.indexOf('/tmp/');
export const isFile = (file: any): file is File =>
  null === file ||
  (typeof file === 'object' && typeof file.originalFilename === 'string' && typeof file.filePath === 'string');
