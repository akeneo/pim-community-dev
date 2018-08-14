import {ConcreteLocale, denormalizeLocale} from 'akeneoenrichedentity/domain/model/locale';

describe('akeneo > enriched entity > domain > model --- locale', () => {
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

  test('I cannot create a new locale with invalid parameters', () => {
    expect(() => {
      new ConcreteLocale({});
    }).toThrow('Locale expect a string as code');

    expect(() => {
      new ConcreteLocale('en_US', {}, []);
    }).toThrow('Locale expect a string as label');

    expect(() => {
      new ConcreteLocale('en_US', 'English (United States)', []);
    }).toThrow('Locale expect a string as region');

    expect(() => {
      new ConcreteLocale('en_US', 'English (United States)', 'United States');
    }).toThrow('Locale expect a string as language');
  });
});
