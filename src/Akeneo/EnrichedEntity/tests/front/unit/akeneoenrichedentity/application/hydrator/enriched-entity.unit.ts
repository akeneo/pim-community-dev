import {hydrator} from 'akeneoenrichedentity/application/hydrator/enriched-entity';
import {
  denormalizeEnrichedEntity,
  createEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

describe('akeneo > enriched entity > application > hydrator --- enriched entity', () => {
  test('I can hydrate a new enriched entity', () => {
    const hydrate = hydrator(({identifier, labels, image}) => {
      expect(identifier).toEqual('designer');
      expect(image).toEqual(null);
      expect(labels).toEqual({en_US: 'Designer'});

      return denormalizeEnrichedEntity({identifier, labels, image});
    });

    expect(hydrate({identifier: 'designer', labels: {en_US: 'Designer'}, image: null}));
  });

  test('It throw an error if I pass a malformed enriched entity', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
