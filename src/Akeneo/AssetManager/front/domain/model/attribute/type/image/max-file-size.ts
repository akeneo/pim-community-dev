import {InvalidArgumentError} from '../image';
import {NormalizableAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/attribute';

export class MaxFileSize implements NormalizableAdditionalProperty {
  private constructor(readonly maxFileSize: string | null) {
    if (!MaxFileSize.isValid(maxFileSize)) {
      throw new InvalidArgumentError('MaxFileSize need to be a valid float or null');
    }
    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    // Regex: assert that the string start with an optional series of figures, an optional
    // point and ends with an optional series of figures
    // Examples:
    // 1.234 -> match
    // .345 -> match
    // 123. -> match
    // 123.344. -> no match
    // 12e3.45 -> no match
    return (
      null === value || (typeof value === 'string' && value.length > 0 && null !== value.match(/^[0-9]*\.?[0-9]*$/))
    );
  }

  public static createFromNormalized(normalizedMaxFileSize: NormalizedMaxFileSize) {
    return new MaxFileSize(normalizedMaxFileSize);
  }

  public normalize(): NormalizedMaxFileSize {
    return this.maxFileSize;
  }

  public static createFromString(maxFileSize: string) {
    return new MaxFileSize('' === maxFileSize ? null : maxFileSize);
  }

  public stringValue(): string {
    return null === this.maxFileSize ? '' : this.maxFileSize.toString();
  }

  public isNull(): boolean {
    return null === this.maxFileSize;
  }
}
export type NormalizedMaxFileSize = string | null;
