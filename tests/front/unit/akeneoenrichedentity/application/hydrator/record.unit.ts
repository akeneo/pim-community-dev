import {hydrator} from 'akeneoenrichedentity/application/hydrator/record';

describe('akeneo > enriched entity > application > hydrator --- record', () => {
  test('I can hydrate a new record', () => {
    const hydrate = hydrator(
      (identifier, enrichedEntityIdentifier, labelCollection) => {
        expect(identifier).toEqual('stark');
        expect(enrichedEntityIdentifier).toEqual('designer');
        expect(labelCollection).toEqual({en_US: 'Stark'});
      },
      identifier => {
        expect(identifier).toEqual('stark');

        return identifier;
      },
      enrichedEntityIdentifier => {
        expect(enrichedEntityIdentifier).toEqual('designer');

        return enrichedEntityIdentifier;
      },
      labelCollection => {
        expect(labelCollection).toEqual({en_US: 'Stark'});

        return labelCollection;
      }
    );

    expect(hydrate({identifier: 'stark', enriched_entity_identifier: 'designer', labels: {en_US: 'Stark'}}));
  });

  test('It throw an error if I pass a malformed record', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'stark'})).toThrow();
    expect(() => hydrator()({enrichedEntityIdentifier: 'designer'})).toThrow();
  });
});
