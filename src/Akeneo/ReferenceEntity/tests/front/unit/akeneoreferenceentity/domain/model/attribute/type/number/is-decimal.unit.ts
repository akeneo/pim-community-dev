import {IsDecimal} from 'akeneoreferenceentity/domain/model/attribute/type/number/is-decimal';

describe('akeneo > attribute > domain > model > attribute > type > number --- IsDecimal', () => {
  test('I can create a IsDecimal from normalized', () => {
    expect(IsDecimal.createFromNormalized(false).normalize()).toEqual(false);
    expect(IsDecimal.createFromNormalized(true).normalize()).toEqual(true);
    expect(() => IsDecimal.createFromNormalized('true')).toThrow();
  });
  test('I can validate a IsDecimal', () => {
    expect(IsDecimal.isValid(true)).toEqual(true);
    expect(IsDecimal.isValid(false)).toEqual(true);
    expect(IsDecimal.isValid('12')).toEqual(false);
    expect(IsDecimal.isValid('1')).toEqual(false);
    expect(IsDecimal.isValid(1)).toEqual(false);
    expect(IsDecimal.isValid(0)).toEqual(false);
    expect(IsDecimal.isValid(undefined)).toEqual(false);
    expect(IsDecimal.isValid({})).toEqual(false);
  });
  test('I can create a IsDecimal from boolean', () => {
    expect(IsDecimal.createFromBoolean(true).booleanValue()).toEqual(true);
    expect(IsDecimal.createFromBoolean(false).booleanValue()).toEqual(false);
  });
});
