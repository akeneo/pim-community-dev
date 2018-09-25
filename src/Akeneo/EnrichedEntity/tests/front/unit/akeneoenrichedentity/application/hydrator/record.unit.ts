import hydrator from 'akeneoenrichedentity/application/hydrator/record';

describe('akeneo > enriched entity > application > hydrator --- record', () => {
  test('I can hydrate a new record', () => {
    expect(
      hydrator({
        identifier: 'designer_starck_fingerprint',
        enriched_entity_identifier: 'designer',
        code: 'starck',
        labels: {en_US: 'Stark'},
        image: null,
        values: [],
      })
    );
  });

  test('It throw an error if I pass a malformed record', () => {
    expect(() => hydrator({})).toThrow();
    expect(() => hydrator({labels: {}})).toThrow();
    expect(() => hydrator({identifier: 'starck'})).toThrow();
    expect(() => hydrator({enrichedEntityIdentifier: 'designer'})).toThrow();
  });
});
