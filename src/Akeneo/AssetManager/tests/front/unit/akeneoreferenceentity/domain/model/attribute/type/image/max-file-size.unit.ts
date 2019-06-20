import {MaxFileSize} from 'akeneoreferenceentity/domain/model/attribute/type/image/max-file-size';

describe('akeneo > attribute > domain > model > attribute > type > text --- MaxFileSize', () => {
  test('I can create a MaxFileSize from normalized', () => {
    expect(MaxFileSize.createFromNormalized('12.2').normalize()).toEqual('12.2');
    expect(MaxFileSize.createFromNormalized(null).normalize()).toEqual(null);
    expect(MaxFileSize.createFromNormalized(null).stringValue()).toEqual('');
    expect(() => MaxFileSize.createFromNormalized('null')).toThrow();
  });
  test('I can validate a MaxFileSize', () => {
    expect(MaxFileSize.isValid('12.2')).toEqual(true);
    expect(MaxFileSize.isValid(null)).toEqual(true);
    expect(MaxFileSize.isValid('12')).toEqual(true);
    expect(MaxFileSize.isValid('12.3')).toEqual(true);
    expect(MaxFileSize.isValid('12.3a')).toEqual(false);
    expect(MaxFileSize.isValid('a')).toEqual(false);
    expect(MaxFileSize.isValid('a12')).toEqual(false);
    expect(MaxFileSize.isValid({test: 'toto'})).toEqual(false);
  });
  test('I can create a MaxFileSize from string', () => {
    expect(MaxFileSize.createFromString('12.2').normalize()).toEqual('12.2');
    expect(MaxFileSize.createFromString('.3').normalize()).toEqual('.3');
    expect(MaxFileSize.createFromString('12.').normalize()).toEqual('12.');
    expect(MaxFileSize.createFromString('').normalize()).toEqual(null);
    expect(MaxFileSize.createFromString('').stringValue()).toEqual('');
    expect(MaxFileSize.createFromString('12').stringValue()).toEqual('12');
    expect(() => MaxFileSize.createFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MaxFileSize is null', () => {
    expect(MaxFileSize.createFromString('12.4').isNull()).toEqual(false);
    expect(MaxFileSize.createFromString('').isNull()).toEqual(true);
    expect(MaxFileSize.createFromNormalized('12.4').isNull()).toEqual(false);
    expect(MaxFileSize.createFromNormalized(null).isNull()).toEqual(true);
  });
});
