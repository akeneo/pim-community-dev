import {hydrator} from 'akeneoreferenceentity/application/hydrator/reference-entity';
import {
  denormalizeReferenceEntity,
  createReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

describe('akeneo > enriched entity > application > hydrator --- enriched entity', () => {
  test('I can hydrate a new enriched entity', () => {
    const hydrate = hydrator(({identifier, labels, image}) => {
      expect(identifier).toEqual('designer');
      expect(image).toEqual(null);
      expect(labels).toEqual({en_US: 'Designer'});

      return denormalizeReferenceEntity({identifier, labels, image});
    });

    expect(hydrate({identifier: 'designer', labels: {en_US: 'Designer'}, image: null}));
  });

  test('It throw an error if I pass a malformed enriched entity', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
