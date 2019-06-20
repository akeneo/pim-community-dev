import {ConcreteLocale, denormalizeLocale, createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';

describe('akeneo > reference entity > domain > model --- locale', () => {
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
      new ConcreteLocale({});
    }).toThrow('Locale expects a string as code');

    expect(() => {
      new ConcreteLocale('en_US', {}, []);
    }).toThrow('Locale expects a string as label');

    expect(() => {
      new ConcreteLocale('en_US', 'English (United States)', []);
    }).toThrow('Locale expects a string as region');

    expect(() => {
      new ConcreteLocale('en_US', 'English (United States)', 'United States');
    }).toThrow('Locale expects a string as language');

    expect(() => {
      const locale = createLocaleFromCode(12);
    }).toThrow('CreateLocaleFromCode expects a string as parameter (number given');
  });
});
