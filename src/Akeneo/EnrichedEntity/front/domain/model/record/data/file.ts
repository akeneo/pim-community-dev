import File, {NormalizedFile, denormalizeFile} from 'akeneoenrichedentity/domain/model/file';
import Data from 'akeneoenrichedentity/domain/model/record/data';

class InvalidTypeError extends Error {}

export type NormalizedFileData = NormalizedFile;

export default class FileData extends Data {
  private constructor(readonly fileData: File) {
    super();

    if (!(fileData instanceof File)) {
      throw new InvalidTypeError('FileData expect a File as parameter to be created');
    }

    Object.freeze(this);
  }

  public static create(fileData: File): FileData {
    return new FileData(fileData);
  }

  public static createFromNormalized(normalizedFileData: NormalizedFileData): FileData {
    return new FileData(denormalizeFile(normalizedFileData));
  }

  public normalize(): NormalizedFileData {
    return this.fileData.normalize();
  }
}

export const create = FileData.create;
export const denormalize = FileData.createFromNormalized;
