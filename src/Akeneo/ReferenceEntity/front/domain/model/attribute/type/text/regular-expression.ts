import {InvalidArgumentError} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {NormalizableAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export class RegularExpression implements NormalizableAdditionalProperty {
  private constructor(readonly regularExpression: string | null) {
    if (!RegularExpression.isValid(regularExpression)) {
      throw new InvalidArgumentError('RegularExpression need to be a valid string or null');
    }
    Object.freeze(this);
  }
  public static isValid(value: any): boolean {
    return null === value || (typeof value === 'string' && value.length > 0);
  }
  public static createFromNormalized(normalizedRegularExpression: NormalizedRegularExpression) {
    return new RegularExpression(normalizedRegularExpression);
  }
  public normalize(): NormalizedRegularExpression {
    return this.regularExpression;
  }
  public static createFromString(regularExpression: string) {
    return new RegularExpression('' === regularExpression ? null : regularExpression);
  }
  public stringValue(): string {
    return null === this.regularExpression ? '' : this.regularExpression.toString();
  }
  public isNull(): boolean {
    return null === this.regularExpression;
  }
}
export type NormalizedRegularExpression = string | null;
