import {InvalidArgumentError} from 'akeneoreferenceentity/domain/model/attribute/type/number';

export type NormalizedMinValue = string | null;

export class MinValue {
  public constructor(readonly minValue: string | null) {
    if (null !== minValue && typeof minValue !== 'string') {
      throw new InvalidArgumentError('MinValue needs to be a string');
    }
    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return (typeof value === 'string' && !isNaN(Number(value))) || null === value || '-' === value;
  }

  public static createFromNormalized(normalizedMinValue: NormalizedMinValue) {
    return new MinValue(normalizedMinValue);
  }

  public normalize(): NormalizedMinValue {
    return this.minValue;
  }

  public static createFromString(minValue: string) {
    return MinValue.createFromNormalized(minValue);
  }

  public stringValue(): string {
    return null === this.minValue ? '' : this.minValue.toString();
  }

  public isNull(): boolean {
    return null === this.minValue || '' === this.minValue;
  }
}
