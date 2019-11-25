export type FilePath = string;
export type File = {
  filePath: FilePath;
  originalFilename: string;
  size?: number;
  mimeType?: string;
  extension?: string;
} | null;

export const createEmptyFile = (): File => null;
export const createFileFromNormalized = (file: any): File => {
  return null === file
    ? null
    : {
        ...file,
      }; // should do the check later, we will see how to handle it in the future
};
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
