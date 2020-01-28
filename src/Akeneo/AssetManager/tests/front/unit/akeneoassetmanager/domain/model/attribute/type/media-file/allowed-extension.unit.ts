import {
  isValidAllowedExtension,
  createAllowedExtensionFromNormalized,
  createAllowedExtensionFromArray,
  normalizeAllowedExtension,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file/allowed-extensions';

describe('akeneo > attribute > domain > model > attribute > type > media-file --- AllowedExtensions', () => {
  test('I can create a AllowedExtensions from normalized', () => {
    expect(createAllowedExtensionFromNormalized(['png', 'jpg', '.pdf', 'JPEG', 'jpeg'])).toEqual([
      'png',
      'jpg',
      'pdf',
      'jpeg',
    ]);
    expect(createAllowedExtensionFromNormalized([])).toEqual([]);
    expect(() => createAllowedExtensionFromNormalized('true')).toThrow();
  });
  test('I can validate a AllowedExtensions', () => {
    expect(isValidAllowedExtension([])).toEqual(true);
    expect(isValidAllowedExtension(['jpeg', 'png'])).toEqual(true);
    expect(isValidAllowedExtension(['jped', 'webm'])).toEqual(true);
    expect(isValidAllowedExtension('12')).toEqual(false);
    expect(isValidAllowedExtension('1')).toEqual(false);
    expect(isValidAllowedExtension(1)).toEqual(false);
    expect(isValidAllowedExtension(0)).toEqual(false);
    expect(isValidAllowedExtension(undefined)).toEqual(false);
    expect(isValidAllowedExtension({})).toEqual(false);
  });
  test('I can create a AllowedExtensions from array', () => {
    expect(normalizeAllowedExtension(createAllowedExtensionFromArray([]))).toEqual([]);
    expect(normalizeAllowedExtension(createAllowedExtensionFromArray(['jpg']))).toEqual(['jpg']);
  });
});
