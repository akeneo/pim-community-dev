export type NormalizedFile = {
  filePath: string;
  originalFilename: string;
  size?: number;
  mimeType?: string;
  extension?: string;
} | null;

class InvalidTypeError extends Error {}
class InvalidCallError extends Error {}

export default class File {
  protected constructor(
    private filePath?: string,
    private originalFilename?: string,
    private size?: number,
    private mimeType?: string,
    private extension?: string
  ) {
    Object.freeze(this);

    if (undefined === filePath && undefined === originalFilename) {
      return;
    }

    if (!('string' === typeof filePath && 0 !== filePath.length)) {
      throw new InvalidTypeError('File expects a non empty string as filePath to be created');
    }
    if (!('string' === typeof originalFilename && 0 !== originalFilename.length)) {
      throw new InvalidTypeError('File expects a non empty string as originalFilename to be created');
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

  public isInStorage(): boolean {
    return undefined !== this.filePath && -1 === this.filePath.indexOf('/tmp/');
  }

  public getSize(): number {
    if (undefined === this.size) {
      throw new InvalidCallError('You cannot get the size on an uploaded or empty file');
    }

    return this.size;
  }

  public getMimeType(): string {
    if (undefined === this.mimeType) {
      throw new InvalidCallError('You cannot get the mime type on an uploaded or empty file');
    }

    return this.mimeType;
  }

  public getExtension(): string {
    if (undefined === this.extension) {
      throw new InvalidCallError('You cannot get the extension on an uploaded or empty file');
    }

    return this.extension;
  }

  public static create(
    filePath: string,
    originalFilename: string,
    size?: number,
    mimeType?: string,
    extension?: string
  ): File {
    return new File(filePath, originalFilename, size, mimeType, extension);
  }

  public static createEmpty(): File {
    return new File();
  }

  public isEmpty(): boolean {
    return undefined === this.filePath || undefined === this.originalFilename;
  }

  public equals(file: File): boolean {
    return (
      file instanceof File &&
      file.filePath === this.filePath &&
      file.originalFilename === this.originalFilename &&
      file.size === this.size &&
      file.mimeType === this.mimeType &&
      file.extension === this.extension
    );
  }

  public static createFromNormalized(normalizedFile: NormalizedFile): File {
    if (null === normalizedFile) {
      return File.createEmpty();
    }

    if (
      normalizedFile.filePath &&
      normalizedFile.originalFilename &&
      normalizedFile.size &&
      normalizedFile.mimeType &&
      normalizedFile.extension
    ) {
      return File.create(
        normalizedFile.filePath,
        normalizedFile.originalFilename,
        normalizedFile.size,
        normalizedFile.mimeType,
        normalizedFile.extension
      );
    }

    return File.create(normalizedFile.filePath, normalizedFile.originalFilename);
  }

  normalize(): NormalizedFile {
    if (this.isEmpty()) {
      return null;
    }

    if (this.size && this.mimeType && this.extension) {
      return {
        filePath: this.filePath as string,
        originalFilename: this.originalFilename as string,
        size: this.size,
        mimeType: this.mimeType,
        extension: this.extension,
      };
    }

    return {
      filePath: this.filePath as string,
      originalFilename: this.originalFilename as string,
    };
  }
}

export const createFile = File.create;
export const createEmptyFile = File.createEmpty;
export const denormalizeFile = File.createFromNormalized;
