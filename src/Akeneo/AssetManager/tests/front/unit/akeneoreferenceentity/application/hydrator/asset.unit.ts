import hydrator from 'akeneoreferenceentity/application/hydrator/record';

describe('akeneo > reference entity > application > hydrator --- record', () => {
  test('I can hydrate a new record', () => {
    expect(
      hydrator({
        identifier: 'designer_starck_fingerprint',
        reference_entity_identifier: 'designer',
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
    expect(() => hydrator({referenceEntityIdentifier: 'designer'})).toThrow();
  });
});
