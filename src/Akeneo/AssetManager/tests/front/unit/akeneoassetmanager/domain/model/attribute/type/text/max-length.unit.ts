import {MaxLength} from 'akeneoassetmanager/domain/model/attribute/type/text/max-length';

describe('akeneo > attribute > domain > model > attribute > type > text --- MaxLength', () => {
  test('I can create a MaxLength from normalized', () => {
    expect(MaxLength.createFromNormalized(12).normalize()).toEqual(12);
    expect(MaxLength.createFromNormalized(null).normalize()).toEqual(null);
    expect(MaxLength.createFromNormalized(null).stringValue()).toEqual('');
    expect(() => MaxLength.createFromNormalized('null')).toThrow();
  });
  test('I can validate a MaxLength', () => {
    expect(MaxLength.isValid(12)).toEqual(true);
    expect(MaxLength.isValid(null)).toEqual(true);
    expect(MaxLength.isValid('12')).toEqual(true);
    expect(MaxLength.isValid('12.3')).toEqual(true);
    expect(MaxLength.isValid('12.3a')).toEqual(true);
    expect(MaxLength.isValid('a')).toEqual(false);
    expect(MaxLength.isValid('a12')).toEqual(false);
    expect(MaxLength.isValid({test: 'toto'})).toEqual(false);
  });
  test('I can create a MaxLength from string', () => {
    expect(MaxLength.createFromString('12').normalize()).toEqual(12);
    expect(MaxLength.createFromString('').normalize()).toEqual(null);
    expect(MaxLength.createFromString('').stringValue()).toEqual('');
    expect(MaxLength.createFromString('12').stringValue()).toEqual('12');
    expect(() => MaxLength.createFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MaxLength is null', () => {
    expect(MaxLength.createFromString('12').isNull()).toEqual(false);
    expect(MaxLength.createFromString('').isNull()).toEqual(true);
    expect(MaxLength.createFromNormalized(12).isNull()).toEqual(false);
    expect(MaxLength.createFromNormalized(null).isNull()).toEqual(true);
  });
});
