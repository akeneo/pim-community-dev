import {hydrator} from 'akeneoenrichedentity/application/hydrator/enriched-entity';

describe('akeneo > enriched entity > application > hydrator --- enriched entity', () => {
  test('I can hydrate a new enriched entity', () => {
    const hydrate = hydrator(
      (identifier, labelCollection) => {
        expect(identifier).toEqual('designer');
        expect(labelCollection).toEqual({en_US: 'Designer'});
      },
      identifier => {
        expect(identifier).toEqual('designer');

        return identifier;
      },
      labelCollection => {
        expect(labelCollection).toEqual({en_US: 'Designer'});

        return labelCollection;
      }
    );

    expect(hydrate({identifier: 'designer', labels: {en_US: 'Designer'}}));
  });

  test('It throw an error if I pass a malformed enriched entity', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'sofa'})).toThrow();
  });
});
