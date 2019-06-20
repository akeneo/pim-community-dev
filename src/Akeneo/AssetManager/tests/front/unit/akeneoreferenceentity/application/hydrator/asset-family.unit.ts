import {hydrator} from 'akeneoassetmanager/application/hydrator/asset-family';
import {denormalizeAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

describe('akeneo > asset family > application > hydrator --- asset family', () => {
  test('I can hydrate a new asset family', () => {
    const hydrate = hydrator(({identifier, labels, image, attribute_as_label, attribute_as_image}) => {
      expect(identifier).toEqual('designer');
      expect(image).toEqual(null);
      expect(labels).toEqual({en_US: 'Designer'});
      expect(attribute_as_label).toEqual('name');
      expect(attribute_as_image).toEqual('picture');

      return denormalizeAssetFamily({identifier, labels, image, attribute_as_label, attribute_as_image});
    });

    expect(
      hydrate({
        identifier: 'designer',
        labels: {en_US: 'Designer'},
        image: null,
        attribute_as_label: 'name',
        attribute_as_image: 'picture',
      })
    );
  });

  test('It throw an error if I pass a malformed asset family', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
