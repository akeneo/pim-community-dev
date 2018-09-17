export type NormalizedFile = {
  filePath: string;
  originalFilename: string;
} | null;

class InvalidTypeError extends Error {}
class InvalidCallError extends Error {}

export default class File {
  protected constructor(private filePath?: string, private originalFilename?: string) {
    Object.freeze(this);

    if (undefined === filePath && undefined === originalFilename) {
      return;
    }

    if (!('string' === typeof filePath && 0 !== filePath.length)) {
      throw new InvalidTypeError('File expect a non empty string as filePath to be created');
    }
    if (!('string' === typeof originalFilename && 0 !== originalFilename.length)) {
      throw new InvalidTypeError('File expect a non empty string as originalFilename to be created');
    }
  }

  public getFilePath(): string {
    if (undefined === this.filePath) {
      throw new InvalidCallError('You cannot get the file path on an empty file');
    }

    return this.filePath;
  }

  public getOriginalFilename(): string {
    if (undefined === this.originalFilename) {
      throw new InvalidCallError('You cannot get the original filename on an empty file');
    }

    return this.originalFilename;
  }

  public static create(filePath: string, originalFilename: string): File {
    return new File(filePath, originalFilename);
  }

  public static createEmpty(): File {
    return new File();
  }

  public isEmpty(): boolean {
    return undefined === this.filePath || undefined === this.originalFilename;
  }

  public static createFromNormalized(normalizedFile: NormalizedFile): File {
    if (null === normalizedFile) {
      return File.createEmpty();
    }

    return File.create(normalizedFile.filePath, normalizedFile.originalFilename);
  }

  normalize(): NormalizedFile {
    return !this.isEmpty()
      ? {
          filePath: this.filePath as string,
          originalFilename: this.originalFilename as string,
        }
      : null;
  }
}

export const createFile = File.create;
export const createEmptyFile = File.createEmpty;
export const denormalizeFile = File.createFromNormalized;
