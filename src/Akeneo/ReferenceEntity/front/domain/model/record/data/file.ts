import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import Data from 'akeneoreferenceentity/domain/model/record/data';

class InvalidTypeError extends Error {}

export type NormalizedFileData = NormalizedFile;

class FileData extends Data {
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

  public getFile() {
    return this.fileData;
  }

  public isEmpty(): boolean {
    return this.fileData.isEmpty();
  }

  public equals(data: Data): boolean {
    return data instanceof FileData && this.fileData.equals(data.fileData);
  }

  public normalize(): NormalizedFileData {
    return this.fileData.normalize();
  }
}

export default FileData;
export const create = FileData.create;
export const denormalizeData = FileData.createFromNormalized;
