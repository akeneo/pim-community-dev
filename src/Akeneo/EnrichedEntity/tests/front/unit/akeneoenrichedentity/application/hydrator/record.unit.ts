import {hydrator} from 'akeneoenrichedentity/application/hydrator/record';

describe('akeneo > enriched entity > application > hydrator --- record', () => {
  test('I can hydrate a new record', () => {
    const hydrate = hydrator(
      (identifier, enrichedEntityIdentifier, code, labelCollection) => {
        expect(identifier).toEqual('designer_starck_fingerprint');
        expect(code).toEqual('starck');
        expect(enrichedEntityIdentifier).toEqual('designer');
        expect(labelCollection).toEqual({en_US: 'Stark'});
      },
      identifier => {
        expect(identifier).toEqual('designer_starck_fingerprint');

        return identifier;
      },
      enrichedEntityIdentifier => {
        expect(enrichedEntityIdentifier).toEqual('designer');

        return enrichedEntityIdentifier;
      },
      code => {
        expect(code).toEqual('starck');

        return code;
      },
      labelCollection => {
        expect(labelCollection).toEqual({en_US: 'Stark'});

        return labelCollection;
      }
    );

    expect(
      hydrate({
        identifier: 'designer_starck_fingerprint',
        enriched_entity_identifier: 'designer',
        code: 'starck',
        labels: {en_US: 'Stark'},
      })
    );
  });

  test('It throw an error if I pass a malformed record', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'starck'})).toThrow();
    expect(() => hydrator()({enrichedEntityIdentifier: 'designer'})).toThrow();
  });
});
