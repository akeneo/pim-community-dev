import {hydrator} from 'akeneoreferenceentity/application/hydrator/reference-entity';
import {
  denormalizeReferenceEntity,
  createReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

describe('akeneo > reference entity > application > hydrator --- reference entity', () => {
  test('I can hydrate a new reference entity', () => {
    const hydrate = hydrator(({identifier, labels, image, attribute_as_label, attribute_as_image}) => {
      expect(identifier).toEqual('designer');
      expect(image).toEqual(null);
      expect(labels).toEqual({en_US: 'Designer'});
      expect(attribute_as_label).toEqual(null);
      expect(attribute_as_image).toEqual(null);

      return denormalizeReferenceEntity({identifier, labels, image, attribute_as_label, attribute_as_image});
    });

    expect(
      hydrate({
        identifier: 'designer',
        labels: {en_US: 'Designer'},
        image: null,
        attribute_as_label: null,
        attribute_as_image: null,
      })
    );
  });

  test('It throw an error if I pass a malformed reference entity', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
