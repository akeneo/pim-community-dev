import {denormalizeLocale, createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';

describe('akeneo > asset family > domain > model --- locale', () => {
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
    }).toThrow('Locale expects a string as code to be created');

    expect(() => {
      denormalizeLocale({code: 'en_US'});
    }).toThrow('Locale expects a string as label to be created');

    expect(() => {
      denormalizeLocale({code: 'en_US', label: 'English (United States)'});
    }).toThrow('Locale expects a string as region to be created');

    expect(() => {
      denormalizeLocale({code: 'en_US', label: 'English (United States)', region: 'United States'});
    }).toThrow('Locale expects a string as language to be created');

    expect(() => {
      const locale = createLocaleFromCode(12);
    }).toThrow('CreateLocaleFromCode expects a string as parameter (number given');
  });
});
