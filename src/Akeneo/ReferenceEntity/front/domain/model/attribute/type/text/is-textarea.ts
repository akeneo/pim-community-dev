import {InvalidArgumentError} from 'akeneoreferenceentity/domain/model/attribute/type/text';

export type NormalizedIsTextarea = boolean;

export class IsTextarea {
  private constructor(readonly isTextarea: boolean) {
    if (!IsTextarea.isValid(isTextarea)) {
      throw new InvalidArgumentError('IsTextarea need to be a boolean');
    }
    Object.freeze(this);
  }
  public static isValid(value: any): boolean {
    return typeof value === 'boolean';
  }
  public static createFromNormalized(normalizedIsTextarea: NormalizedIsTextarea) {
    return new IsTextarea(normalizedIsTextarea);
  }
  public normalize(): NormalizedIsTextarea {
    return this.isTextarea;
  }
  public static createFromBoolean(isTextarea: boolean) {
    return IsTextarea.createFromNormalized(isTextarea);
  }
  public booleanValue(): boolean {
    return this.normalize();
  }
}
