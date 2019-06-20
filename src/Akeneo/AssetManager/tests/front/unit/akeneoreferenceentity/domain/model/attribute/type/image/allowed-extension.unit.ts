import {AllowedExtensions} from 'akeneoassetmanager/domain/model/attribute/type/image/allowed-extensions';

describe('akeneo > attribute > domain > model > attribute > type > image --- AllowedExtensions', () => {
  test('I can create a AllowedExtensions from normalized', () => {
    expect(AllowedExtensions.createFromNormalized(['png', 'jpg']).normalize()).toEqual(['png', 'jpg']);
    expect(AllowedExtensions.createFromNormalized([]).normalize()).toEqual([]);
    expect(() => AllowedExtensions.createFromNormalized('true')).toThrow();
  });
  test('I can validate a AllowedExtensions', () => {
    expect(AllowedExtensions.isValid([])).toEqual(true);
    expect(AllowedExtensions.isValid(['jpeg', 'png'])).toEqual(true);
    expect(AllowedExtensions.isValid(['jped', 'webm'])).toEqual(false);
    expect(AllowedExtensions.isValid('12')).toEqual(false);
    expect(AllowedExtensions.isValid('1')).toEqual(false);
    expect(AllowedExtensions.isValid(1)).toEqual(false);
    expect(AllowedExtensions.isValid(0)).toEqual(false);
    expect(AllowedExtensions.isValid(undefined)).toEqual(false);
    expect(AllowedExtensions.isValid({})).toEqual(false);
  });
  test('I can create a AllowedExtensions from array', () => {
    expect(AllowedExtensions.createFromArray([]).arrayValue()).toEqual([]);
    expect(AllowedExtensions.createFromArray(['jpg']).arrayValue()).toEqual(['jpg']);
  });
});
