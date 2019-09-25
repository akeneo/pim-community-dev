import {
  denormalizeLocaleReference,
  localeReferenceStringValue,
  localeReferenceAreEqual,
  localeReferenceIsEmpty,
} from 'akeneoassetmanager/domain/model/locale-reference';

describe('akeneo > asset family > domain > model --- locale reference', () => {
  test('I can create a new locale reference with a string value', () => {
    expect(localeReferenceStringValue(denormalizeLocaleReference('en_US'))).toBe('en_US');
  });

  test('I can create a new locale reference with a null value', () => {
    expect(localeReferenceStringValue(denormalizeLocaleReference(null))).toBe('');
  });

  test('I cannot create a new locale reference with a value other than a string or null', () => {
    expect(() => {
      denormalizeLocaleReference(12);
    }).toThrow('A locale reference should be a string or null');
  });

  test('I can compare two locale references', () => {
    expect(localeReferenceAreEqual('en_US', 'fr_FR')).toBe(false);
    expect(localeReferenceAreEqual('en_US', 'en_US')).toBe(true);
    expect(localeReferenceAreEqual(null, null)).toBe(true);
    expect(localeReferenceAreEqual('en_US', null)).toBe(false);
  });

  test('I can know if a locale reference is empty', () => {
    expect(localeReferenceIsEmpty('en_US')).toBe(false);
    expect(localeReferenceIsEmpty(null)).toBe(true);
  });

  test('I can get the string value of a locale reference', () => {
    expect(localeReferenceStringValue('en_US')).toBe('en_US');
    expect(localeReferenceStringValue(null)).toBe('');
  });
});
