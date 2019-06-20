import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import ValueData from 'akeneoreferenceentity/domain/model/record/data';

class InvalidTypeError extends Error {}

export type NormalizedFileData = NormalizedFile;

class FileData extends ValueData {
  private constructor(readonly fileData: File) {
    super();

    if (!(fileData instanceof File)) {
      throw new InvalidTypeError('FileData expects a File as parameter to be created');
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

  public equals(data: ValueData): boolean {
    return data instanceof FileData && this.fileData.equals(data.fileData);
  }

  public normalize(): NormalizedFileData {
    return this.fileData.normalize();
  }
}

export default FileData;
export const create = FileData.create;
export const denormalize = FileData.createFromNormalized;
