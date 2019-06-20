import {hydrator} from 'akeneoassetmanager/application/hydrator/channel';

describe('akeneo > asset family > application > hydrator --- channel', () => {
  test('It throw an error if I pass a malformed channel', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({label: {}})).toThrow();
    expect(() => hydrator()({identifier: 'starck'})).toThrow();
  });

  test('I can hydrate a channel', () => {
    expect(hydrator(() => null)({labels: {}, code: 'ecommerce', locales: []})).toEqual(null);
  });
});
