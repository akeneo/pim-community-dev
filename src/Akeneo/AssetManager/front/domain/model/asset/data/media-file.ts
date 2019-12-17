import {File, createFileFromNormalized, isFileEmpty, areFilesEqual} from 'akeneoassetmanager/domain/model/file';
import ValueData from 'akeneoassetmanager/domain/model/asset/data';

export type NormalizedFileData = File;

class FileData extends ValueData {
  private constructor(readonly fileData: File) {
    super();

    Object.freeze(this);
  }

  public static create(fileData: File): FileData {
    return new FileData(fileData);
  }

  public static createFromNormalized(normalizedFileData: NormalizedFileData): FileData {
    return new FileData(createFileFromNormalized(normalizedFileData));
  }

  public getFile() {
    return this.fileData;
  }

  public isEmpty(): boolean {
    return isFileEmpty(this.fileData);
  }

  public equals(data: ValueData): boolean {
    return data instanceof FileData && areFilesEqual(this.fileData, data.fileData);
  }

  public normalize(): NormalizedFileData {
    return this.fileData;
  }
}

export default FileData;
export const create = FileData.create;
export const denormalize = FileData.createFromNormalized;
