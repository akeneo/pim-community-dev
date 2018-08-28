import {NormalizableAdditionalProperty} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {InvalidArgumentError} from 'akeneoenrichedentity/domain/model/attribute/type/text';

export enum ValidationRuleOption {
  Email = 'email',
  RegularExpression = 'regular_expression',
  Url = 'url',
  None = 'none',
}
export type NormalizedValidationRule = ValidationRuleOption;

export class ValidationRule implements NormalizableAdditionalProperty {
  private constructor(readonly validationRule: ValidationRuleOption) {
    if (!ValidationRule.isValid(validationRule)) {
      throw new InvalidArgumentError(
        `ValidationRule need to be a valid validation rule (${Object.values(ValidationRuleOption).join(', ')})`
      );
    }
    Object.freeze(this);
  }
  public static isValid(value: any): boolean {
    return typeof value === 'string' && Object.values(ValidationRuleOption).includes(value);
  }
  public static createFromNormalized(normalizedValidationRule: NormalizedValidationRule) {
    return new ValidationRule(normalizedValidationRule);
  }
  public normalize(): NormalizedValidationRule {
    return this.validationRule;
  }
  public static createFromString(validationRule: string) {
    return ValidationRule.createFromNormalized(validationRule as ValidationRuleOption);
  }
  public stringValue(): string {
    return this.validationRule;
  }
}
