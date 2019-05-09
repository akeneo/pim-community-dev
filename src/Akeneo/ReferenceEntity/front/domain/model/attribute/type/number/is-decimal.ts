import {InvalidArgumentError} from "../number";

export type NormalizedIsDecimal = boolean;

export class IsDecimal {
  public constructor(readonly isDecimal: boolean) {
    if (!IsDecimal.isValid(isDecimal)) {
      throw new InvalidArgumentError('IsDecimal need to be a boolean');
    }
    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return typeof value === 'boolean';
  }

  public static createFromNormalized(normalizedIsDecimal: NormalizedIsDecimal) {
    return new IsDecimal(normalizedIsDecimal);
  }

  public normalize(): NormalizedIsDecimal {
    return this.isDecimal;
  }

  public static createFromBoolean(isDecimal: boolean) {
    return IsDecimal.createFromNormalized(isDecimal);
  }

  public booleanValue(): boolean {
    return this.normalize();
  }
}
