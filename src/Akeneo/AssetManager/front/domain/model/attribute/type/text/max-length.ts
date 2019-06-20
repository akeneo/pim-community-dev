import {InvalidArgumentError} from 'akeneoassetmanager/domain/model/attribute/type/text';

export type NormalizedMaxLength = number | null;

export class MaxLength {
  private constructor(readonly maxLength: number | null) {
    if (!(null === maxLength || typeof maxLength === 'number')) {
      throw new InvalidArgumentError('MaxLength need to be a valid integer or null');
    }
    Object.freeze(this);
  }
  public static isValid(value: any): boolean {
    return (!isNaN(parseInt(value)) && 0 < parseInt(value)) || '' === value || null === value;
  }
  public static createFromNormalized(normalizedMaxLength: NormalizedMaxLength) {
    return new MaxLength(normalizedMaxLength);
  }
  public normalize(): NormalizedMaxLength {
    return this.maxLength;
  }
  public static createFromString(maxLength: string) {
    if (!MaxLength.isValid(maxLength)) {
      throw new InvalidArgumentError('MaxLength need to be a valid integer');
    }
    return new MaxLength('' === maxLength ? null : parseInt(maxLength));
  }
  public stringValue(): string {
    return null === this.maxLength ? '' : this.maxLength.toString();
  }
  public isNull(): boolean {
    return null === this.maxLength;
  }
}
