import {
  ValidationRule,
  ValidationRuleOption,
} from 'akeneoassetmanager/domain/model/attribute/type/text/validation-rule';

const options = Object.values(ValidationRuleOption);

describe('akeneo > attribute > domain > model > attribute > type > text --- ValidationRule', () => {
  test('I can create a ValidationRule from normalized', () => {
    options.forEach(option => {
      expect(ValidationRule.createFromNormalized(option).normalize()).toEqual(option);
    });
  });
  test('I can validate a ValidationRule', () => {
    options.forEach(option => {
      expect(ValidationRule.isValid(option)).toEqual(true);
    });
    expect(ValidationRule.isValid('test')).toEqual(false);
    expect(ValidationRule.isValid(12)).toEqual(false);
    expect(ValidationRule.isValid(null)).toEqual(false);
    expect(ValidationRule.isValid({test: 'toto'})).toEqual(false);
  });
  test('I can create a ValidationRule from string', () => {
    options.forEach(option => {
      expect(ValidationRule.createFromString(option).stringValue()).toEqual(option);
    });
    expect(() => ValidationRule.createFromString({my: 'object'})).toThrow();
  });
});
