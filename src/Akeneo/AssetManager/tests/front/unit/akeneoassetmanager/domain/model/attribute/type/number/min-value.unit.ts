import {MinValue} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';

describe('akeneo > attribute > domain > model > attribute > type > text --- MinValue', () => {
  test('I can create a MinValue from normalized', () => {
    expect(MinValue.createFromNormalized('12').normalize()).toEqual('12');
    expect(MinValue.createFromNormalized('').normalize()).toEqual('');
  });
  test('I can validate a MinValue', () => {
    expect(MinValue.isValid(12)).toEqual(false);
    expect(MinValue.isValid(null)).toEqual(true);
    expect(MinValue.isValid('12')).toEqual(true);
    expect(MinValue.isValid('12.3')).toEqual(true);
    expect(MinValue.isValid('12.3a')).toEqual(false);
    expect(MinValue.isValid('a')).toEqual(false);
    expect(MinValue.isValid('a12')).toEqual(false);
    expect(MinValue.isValid({test: 'toto'})).toEqual(false);
  });
  test('I can create a MinValue from string', () => {
    expect(MinValue.createFromString('12').normalize()).toEqual('12');
    expect(MinValue.createFromString('').normalize()).toEqual('');
    expect(MinValue.createFromString('').stringValue()).toEqual('');
    expect(MinValue.createFromString('12').stringValue()).toEqual('12');
    expect(() => MinValue.createFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MinValue is null', () => {
    expect(MinValue.createFromString('12').isNull()).toEqual(false);
    expect(MinValue.createFromString('').isNull()).toEqual(true);
    expect(MinValue.createFromNormalized('12').isNull()).toEqual(false);
  });
});
