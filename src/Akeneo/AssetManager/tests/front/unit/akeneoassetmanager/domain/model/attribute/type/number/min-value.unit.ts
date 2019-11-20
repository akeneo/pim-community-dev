import {
  isValidMinValue,
  createMinValueFromNormalized,
  createMinValueFromString,
  minValueStringValue,
  isNullMinValue,
} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';

describe('akeneo > attribute > domain > model > attribute > type > text --- MinValue', () => {
  test('I can create a MinValue from normalized', () => {
    expect(createMinValueFromNormalized('12')).toEqual('12');
    expect(createMinValueFromNormalized('')).toEqual('');
  });
  test('I can validate a MinValue', () => {
    expect(isValidMinValue(12)).toEqual(false);
    expect(isValidMinValue(null)).toEqual(true);
    expect(isValidMinValue('12')).toEqual(true);
    expect(isValidMinValue('12.3')).toEqual(true);
    expect(isValidMinValue('12.3a')).toEqual(false);
    expect(isValidMinValue('a')).toEqual(false);
    expect(isValidMinValue('a12')).toEqual(false);
    expect(isValidMinValue({test: 'toto'})).toEqual(false);
  });
  test('I can create a MinValue from string', () => {
    expect(createMinValueFromString('12')).toEqual('12');
    expect(createMinValueFromString('')).toEqual(null);
    expect(minValueStringValue(createMinValueFromString(''))).toEqual('');
    expect(minValueStringValue(createMinValueFromString('12'))).toEqual('12');
    expect(() => createMinValueFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MinValue is null', () => {
    expect(isNullMinValue(createMinValueFromString('12'))).toEqual(false);
    expect(isNullMinValue(createMinValueFromString(''))).toEqual(true);
    expect(isNullMinValue(createMinValueFromNormalized('12'))).toEqual(false);
  });
});
