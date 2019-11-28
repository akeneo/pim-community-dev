import {hydrator} from 'akeneoassetmanager/application/hydrator/asset-family';
import {createAssetFamilyFromNormalized} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

describe('akeneo > asset family > application > hydrator --- asset family', () => {
  test('I can hydrate a new asset family', () => {
    const hydrate = hydrator(({identifier, labels, image, attribute_as_label, attribute_as_main_media}) => {
      expect(identifier).toEqual('designer');
      expect(image).toEqual(null);
      expect(labels).toEqual({en_US: 'Designer'});
      expect(attribute_as_label).toEqual('name');
      expect(attribute_as_main_media).toEqual('picture');

      return createAssetFamilyFromNormalized({identifier, labels, image, attribute_as_label, attribute_as_main_media});
    });

    expect(
      hydrate({
        identifier: 'designer',
        labels: {en_US: 'Designer'},
        image: null,
        attribute_as_label: 'name',
        attribute_as_main_media: 'picture',
      })
    );
  });

  test('It throw an error if I pass a malformed asset family', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
