import {
  createMediaTypeFromNormalized,
  createMediaTypeFromString,
  isValidMediaType,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';

test('I can create a MediaType from normalized', () => {
  expect(createMediaTypeFromNormalized('image')).toEqual('image');
  expect(createMediaTypeFromNormalized('pdf')).toEqual('pdf');
  expect(createMediaTypeFromNormalized('other')).toEqual('other');
});

test('I can validate a MediaType', () => {
  /* @ts-expect-error invalid media type */
  expect(isValidMediaType(null)).toEqual(false);
  /* @ts-expect-error invalid media type */
  expect(isValidMediaType({test: 'toto'})).toEqual(false);
  /* @ts-expect-error invalid media type */
  expect(isValidMediaType(12)).toEqual(false);
  expect(isValidMediaType('12')).toEqual(false);

  expect(isValidMediaType('image')).toEqual(true);
  expect(isValidMediaType('pdf')).toEqual(true);
  expect(isValidMediaType('other')).toEqual(true);
});

test('I can create a MediaType from string', () => {
  expect(createMediaTypeFromString(createMediaTypeFromString('image'))).toEqual('image');
  expect(createMediaTypeFromString(createMediaTypeFromString('pdf'))).toEqual('pdf');
  expect(createMediaTypeFromString(createMediaTypeFromString('other'))).toEqual('other');
  expect(() => createMediaTypeFromString('youtube')).toThrow('MediaType should be image,pdf,other');
});
