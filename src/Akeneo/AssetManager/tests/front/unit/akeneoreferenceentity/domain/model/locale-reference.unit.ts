import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';

describe('akeneo > reference entity > domain > model --- locale reference', () => {
  test('I can create a new locale reference with a string value', () => {
    expect(createLocaleReference('en_US').stringValue()).toBe('en_US');
  });

  test('I can create a new locale reference with a null value', () => {
    expect(createLocaleReference(null).stringValue()).toBe('');
  });

  test('I cannot create a new locale reference with a value other than a string or null', () => {
    expect(() => {
      createLocaleReference(12);
    }).toThrow('LocaleReference expects a string or null as parameter to be created');
  });

  test('I can compare two locale references', () => {
    expect(createLocaleReference('en_US').equals(createLocaleReference('fr_FR'))).toBe(false);
    expect(createLocaleReference('en_US').equals(createLocaleReference('en_US'))).toBe(true);
    expect(createLocaleReference(null).equals(createLocaleReference(null))).toBe(true);
    expect(createLocaleReference('en_US').equals(createLocaleReference(null))).toBe(false);
  });

  test('I can know if a locale reference is empty', () => {
    expect(createLocaleReference('en_US').isEmpty()).toBe(false);
    expect(createLocaleReference(null).isEmpty()).toBe(true);
  });

  test('I can normalize a locale reference', () => {
    expect(createLocaleReference('en_US').normalize()).toBe('en_US');
    expect(createLocaleReference(null).normalize()).toBe(null);
  });

  test('I can get the string value of a locale reference', () => {
    expect(createLocaleReference('en_US').stringValue()).toBe('en_US');
    expect(createLocaleReference(null).stringValue()).toBe('');
  });
});
