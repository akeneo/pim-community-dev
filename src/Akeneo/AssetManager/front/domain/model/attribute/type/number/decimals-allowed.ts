import {InvalidArgumentError} from 'akeneoassetmanager/domain/model/attribute/type/number';

export type NormalizedDecimalsAllowed = boolean;

export class DecimalsAllowed {
  public constructor(readonly decimalsAllowed: boolean) {
    if (!DecimalsAllowed.isValid(decimalsAllowed)) {
      throw new InvalidArgumentError('DecimalsAllowed need to be a boolean');
    }
    Object.freeze(this);
  }

  public static isValid(value: any): boolean {
    return typeof value === 'boolean';
  }

  public static createFromNormalized(normalizedDecimalsAllowed: NormalizedDecimalsAllowed) {
    return new DecimalsAllowed(normalizedDecimalsAllowed);
  }

  public normalize(): NormalizedDecimalsAllowed {
    return this.decimalsAllowed;
  }

  public static createFromBoolean(decimalsAllowed: boolean) {
    return DecimalsAllowed.createFromNormalized(decimalsAllowed);
  }

  public booleanValue(): boolean {
    return this.normalize();
  }
}
