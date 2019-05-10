import {InvalidArgumentError} from 'akeneoreferenceentity/domain/model/attribute/type/number';

export type NormalizedMaxValue = string | null;

export class MaxValue {
  public constructor(readonly maxValue: string | null) {
    if (!(null === maxValue || typeof maxValue === 'string')) {
      throw new InvalidArgumentError('MaxValue need to be a string');
    }
    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return (typeof value === 'string' && !isNaN(Number(value))) || null === value;
  }

  public static createFromNormalized(normalizedMaxValue: NormalizedMaxValue) {
    return new MaxValue(normalizedMaxValue);
  }

  public normalize(): NormalizedMaxValue {
    return this.maxValue;
  }

  public static createFromString(maxValue: string) {
    return MaxValue.createFromNormalized(maxValue);
  }

  public stringValue(): string {
    return null === this.maxValue ? '' : this.maxValue.toString();
  }

  public isNull(): boolean {
    return null === this.maxValue;
  }
}
