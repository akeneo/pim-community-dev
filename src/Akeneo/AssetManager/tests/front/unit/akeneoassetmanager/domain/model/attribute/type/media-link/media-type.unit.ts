import {
  createMediaTypeFromNormalized,
  normalizeMediaType,
  isValidMediaType,
  createMediaTypeFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';

describe('akeneo > attribute > domain > model > attribute > type > media-link --- media-type', () => {
  test('I can create a MediaType from normalized', () => {
    expect(normalizeMediaType(createMediaTypeFromNormalized('image'))).toEqual('image');
    expect(normalizeMediaType(createMediaTypeFromNormalized('pdf'))).toEqual('pdf');
    expect(normalizeMediaType(createMediaTypeFromNormalized('youtube'))).toEqual('youtube');
    expect(normalizeMediaType(createMediaTypeFromNormalized('other'))).toEqual('other');
  });
  test('I can validate a MediaType', () => {
    expect(isValidMediaType(null)).toEqual(false);
    expect(isValidMediaType('12')).toEqual(false);
    expect(isValidMediaType('image')).toEqual(true);
    expect(isValidMediaType('pdf')).toEqual(true);
    expect(isValidMediaType('youtube')).toEqual(true);
    expect(isValidMediaType('other')).toEqual(true);
    expect(isValidMediaType({test: 'toto'})).toEqual(false);
    expect(isValidMediaType(12)).toEqual(false);
  });
  test('I can create a MediaType from string', () => {
    expect(createMediaTypeFromString(createMediaTypeFromString('image'))).toEqual('image');
    expect(createMediaTypeFromString(createMediaTypeFromString('pdf'))).toEqual('pdf');
    expect(createMediaTypeFromString(createMediaTypeFromString('youtube'))).toEqual('youtube');
    expect(createMediaTypeFromString(createMediaTypeFromString('other'))).toEqual('other');
    expect(() => createMediaTypeFromString({my: 'object'})).toThrow();
  });
});
