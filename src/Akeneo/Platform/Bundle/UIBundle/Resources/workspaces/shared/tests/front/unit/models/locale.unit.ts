import {denormalizeLocale, createLocaleFromCode, localeExists} from '../../../../src/models/locale';

describe('akeneo > shared > model --- locale', () => {
  test('I can create a new locale from a normalized one', () => {
    const locale = denormalizeLocale({
      code: 'en_US',
      label: 'English (United States)',
      region: 'United States',
      language: 'English',
    });
    expect(locale.code).toBe('en_US');
    expect(locale.label).toBe('English (United States)');
    expect(locale.language).toBe('English');
    expect(locale.region).toBe('United States');
  });

  test('I can create a new locale from a code', () => {
    const locale = createLocaleFromCode('en_US');
    expect(locale.code).toBe('en_US');
    expect(locale.label).toBe('en_US');
    expect(locale.language).toBe('en');
    expect(locale.region).toBe('us');
  });

  test('I cannot create a new locale with invalid parameters', () => {
    expect(() => {
      denormalizeLocale({});
    }).toThrow('Invalid locale');

    expect(() => {
      denormalizeLocale({code: 'en_US'});
    }).toThrow('Invalid locale');

    expect(() => {
      denormalizeLocale({code: 'en_US', label: 'English (United States)'});
    }).toThrow('Invalid locale');

    expect(() => {
      denormalizeLocale({code: 'en_US', label: 'English (United States)', region: 'United States'});
    }).toThrow('Invalid locale');

    //@ts-ignore
    expect(() => createLocaleFromCode(12)).toThrow('CreateLocaleFromCode expects a string as parameter (number given)');
  });

  test('I tells if a locale exists in a list of locales', () => {
    const expectedLocaleCode = 'en_US';
    const locales = [
      {
        code: expectedLocaleCode,
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
    ];
    expect(localeExists(locales, expectedLocaleCode)).toEqual(true);
    expect(localeExists(locales, 'UNKNOWN_LOCALE_CODE')).toEqual(false);
  });
});
