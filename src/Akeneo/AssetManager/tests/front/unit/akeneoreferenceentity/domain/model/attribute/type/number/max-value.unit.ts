import {MaxValue} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';

describe('akeneo > attribute > domain > model > attribute > type > text --- MaxValue', () => {
  test('I can create a MaxValue from normalized', () => {
    expect(MaxValue.createFromNormalized('12').normalize()).toEqual('12');
    expect(MaxValue.createFromNormalized('').normalize()).toEqual('');
  });
  test('I can validate a MaxValue', () => {
    expect(MaxValue.isValid(12)).toEqual(false);
    expect(MaxValue.isValid(null)).toEqual(true);
    expect(MaxValue.isValid('12')).toEqual(true);
    expect(MaxValue.isValid('12.3')).toEqual(true);
    expect(MaxValue.isValid('12.3a')).toEqual(false);
    expect(MaxValue.isValid('a')).toEqual(false);
    expect(MaxValue.isValid('a12')).toEqual(false);
    expect(MaxValue.isValid({test: 'toto'})).toEqual(false);
  });
  test('I can create a MaxValue from string', () => {
    expect(MaxValue.createFromString('12').normalize()).toEqual('12');
    expect(MaxValue.createFromString('').normalize()).toEqual('');
    expect(MaxValue.createFromString('').stringValue()).toEqual('');
    expect(MaxValue.createFromString('12').stringValue()).toEqual('12');
    expect(() => MaxValue.createFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MaxValue is null', () => {
    expect(MaxValue.createFromString('12').isNull()).toEqual(false);
    expect(MaxValue.createFromString('').isNull()).toEqual(true);
    expect(MaxValue.createFromNormalized('12').isNull()).toEqual(false);
  });
});
