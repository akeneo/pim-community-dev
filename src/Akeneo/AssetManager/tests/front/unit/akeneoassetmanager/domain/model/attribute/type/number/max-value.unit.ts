import {
  isValidMaxValue,
  createMaxValueFromNormalized,
  createMaxValueFromString,
  maxValueStringValue,
  isNullMaxValue,
} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';

describe('akeneo > attribute > domain > model > attribute > type > text --- MaxValue', () => {
  test('I can create a MaxValue from normalized', () => {
    expect(createMaxValueFromNormalized('12')).toEqual('12');
    expect(createMaxValueFromNormalized('')).toEqual('');
  });
  test('I can validate a MaxValue', () => {
    expect(isValidMaxValue(12)).toEqual(false);
    expect(isValidMaxValue(null)).toEqual(true);
    expect(isValidMaxValue('12')).toEqual(true);
    expect(isValidMaxValue('12.3')).toEqual(true);
    expect(isValidMaxValue('12.3a')).toEqual(false);
    expect(isValidMaxValue('a')).toEqual(false);
    expect(isValidMaxValue('a12')).toEqual(false);
    expect(isValidMaxValue({test: 'toto'})).toEqual(false);
  });
  test('I can create a MaxValue from string', () => {
    expect(createMaxValueFromString('12')).toEqual('12');
    expect(createMaxValueFromString('')).toEqual(null);
    expect(maxValueStringValue(createMaxValueFromString(''))).toEqual('');
    expect(maxValueStringValue(createMaxValueFromString('12'))).toEqual('12');
    expect(() => createMaxValueFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MaxValue is null', () => {
    expect(isNullMaxValue(createMaxValueFromString('12'))).toEqual(false);
    expect(isNullMaxValue(createMaxValueFromString(''))).toEqual(true);
    expect(isNullMaxValue(createMaxValueFromNormalized('12'))).toEqual(false);
  });
});
