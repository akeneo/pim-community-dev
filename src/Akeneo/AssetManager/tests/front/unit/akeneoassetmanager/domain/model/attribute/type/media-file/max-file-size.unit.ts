import {
  isValidMaxFileSize,
  createMaxFileSizeFromString,
  createMaxFileSizeFromNormalized,
  maxFileSizeStringValue,
  isNullMaxFileSize,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file/max-file-size';

describe('akeneo > attribute > domain > model > attribute > type > media-file --- MaxFileSize', () => {
  test('I can create a MaxFileSize from normalized', () => {
    expect(createMaxFileSizeFromNormalized('12.2')).toEqual('12.2');
    expect(createMaxFileSizeFromNormalized(null)).toEqual(null);
    expect(maxFileSizeStringValue(createMaxFileSizeFromNormalized(null))).toEqual('');
    expect(() => createMaxFileSizeFromNormalized('null')).toThrow();
  });
  test('I can validate a MaxFileSize', () => {
    expect(isValidMaxFileSize('12.2')).toEqual(true);
    expect(isValidMaxFileSize(null)).toEqual(true);
    expect(isValidMaxFileSize('12')).toEqual(true);
    expect(isValidMaxFileSize('12.3')).toEqual(true);
    expect(isValidMaxFileSize('12.3a')).toEqual(false);
    expect(isValidMaxFileSize('a')).toEqual(false);
    expect(isValidMaxFileSize('a12')).toEqual(false);
    expect(isValidMaxFileSize({test: 'toto'})).toEqual(false);
  });
  test('I can create a MaxFileSize from string', () => {
    expect(createMaxFileSizeFromString('12.2')).toEqual('12.2');
    expect(createMaxFileSizeFromString('.3')).toEqual('.3');
    expect(createMaxFileSizeFromString('12.')).toEqual('12.');
    expect(createMaxFileSizeFromString('')).toEqual(null);
    expect(maxFileSizeStringValue(createMaxFileSizeFromString(''))).toEqual('');
    expect(maxFileSizeStringValue(createMaxFileSizeFromString('12'))).toEqual('12');
    expect(() => createMaxFileSizeFromString({my: 'object'})).toThrow();
  });
  test('I can know if the MaxFileSize is null', () => {
    expect(isNullMaxFileSize(createMaxFileSizeFromString('12.4'))).toEqual(false);
    expect(isNullMaxFileSize(createMaxFileSizeFromString(''))).toEqual(true);
    expect(isNullMaxFileSize(createMaxFileSizeFromNormalized('12.4'))).toEqual(false);
    expect(isNullMaxFileSize(createMaxFileSizeFromNormalized(null))).toEqual(true);
  });
});
