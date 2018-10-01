import {NormalizableAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {InvalidArgumentError} from '../image';

export enum AllowedExtensionsOptions {
  gif = 'gif',
  jfif = 'jfif',
  jif = 'jif',
  jpeg = 'jpeg',
  jpg = 'jpg',
  pdf = 'pdf',
  png = 'png',
  psd = 'psd',
  tif = 'tif',
  tiff = 'tiff',
}
export type NormalizedAllowedExtensions = AllowedExtensionsOptions[];

export class AllowedExtensions implements NormalizableAdditionalProperty {
  private constructor(readonly allowedExtensions: AllowedExtensionsOptions[]) {
    if (!AllowedExtensions.isValid(allowedExtensions)) {
      throw new InvalidArgumentError('AllowedExtensions need to be a valid array of allowed extensions');
    }
    Object.freeze(this);
  }
  public static isValid(value: any): boolean {
    if (!Array.isArray(value)) {
      return false;
    }
    const invalidAllowedExtensions = value.filter(
      (extension: string) => !Object.values(AllowedExtensionsOptions).includes(extension)
    );
    return 0 === invalidAllowedExtensions.length;
  }
  public static createFromNormalized(normalizedAllowedExtensions: NormalizedAllowedExtensions) {
    return new AllowedExtensions(normalizedAllowedExtensions);
  }
  public normalize(): NormalizedAllowedExtensions {
    return this.allowedExtensions;
  }
  public static createFromArray(allowedExtensions: string[]) {
    return new AllowedExtensions(allowedExtensions as AllowedExtensionsOptions[]);
  }
  public arrayValue(): string[] {
    return this.allowedExtensions;
  }
}
