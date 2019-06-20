import {hydrator} from 'akeneoassetmanager/application/hydrator/locale';

describe('akeneo > asset family > application > hydrator --- locale', () => {
  test('I can hydrate a new locale', () => {
    const hydrate = hydrator(({code, label, region, language}) => {
      expect(code).toEqual('en_US');
      expect(label).toEqual('English (United States)');
      expect(region).toEqual('United States');
      expect(language).toEqual('English');
    });

    expect(
      hydrate({
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      })
    );
  });

  test('It throw an error if I pass a malformed locale', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'starck'})).toThrow();
  });
});
