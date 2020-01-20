import {hydrator} from 'akeneoassetmanager/application/hydrator/asset-family';

describe('akeneo > asset family > application > hydrator --- asset family', () => {
  test('I can hydrate a new asset family', () => {
    const hydrate = hydrator();

    expect(
      hydrate({
        identifier: 'designer',
        labels: {en_US: 'Designer'},
        image: null,
        attribute_as_label: 'name',
        attribute_as_main_media: 'picture',
        asset_count: 0,
        attributes: [],
        permission: {},
      })
    );
  });

  test('It throw an error if I pass a malformed asset family', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
