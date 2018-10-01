import {RegularExpression} from 'akeneoreferenceentity/domain/model/attribute/type/text/regular-expression';

describe('akeneo > attribute > domain > model > attribute > type > text --- RegularExpression', () => {
  test('I can create a RegularExpression from normalized', () => {
    expect(RegularExpression.createFromNormalized('12').normalize()).toEqual('12');
    expect(RegularExpression.createFromNormalized(null).normalize()).toEqual(null);
    expect(RegularExpression.createFromNormalized(null).stringValue()).toEqual('');
  });
  test('I can validate a RegularExpression', () => {
    expect(RegularExpression.isValid(null)).toEqual(true);
    expect(RegularExpression.isValid('12')).toEqual(true);
    expect(RegularExpression.isValid('12.3')).toEqual(true);
    expect(RegularExpression.isValid('12.3a')).toEqual(true);
    expect(RegularExpression.isValid({test: 'toto'})).toEqual(false);
    expect(RegularExpression.isValid(12)).toEqual(false);
  });
  test('I can create a RegularExpression from string', () => {
    expect(RegularExpression.createFromString('12').normalize()).toEqual('12');
    expect(RegularExpression.createFromString('').normalize()).toEqual(null);
    expect(RegularExpression.createFromString('').stringValue()).toEqual('');
    expect(RegularExpression.createFromString('12').stringValue()).toEqual('12');
    expect(() => RegularExpression.createFromString({my: 'object'})).toThrow();
  });
  test('I can know if the RegularExpression is null', () => {
    expect(RegularExpression.createFromString('12').isNull()).toEqual(false);
    expect(RegularExpression.createFromString('').isNull()).toEqual(true);
    expect(RegularExpression.createFromNormalized(null).isNull()).toEqual(true);
  });
});
