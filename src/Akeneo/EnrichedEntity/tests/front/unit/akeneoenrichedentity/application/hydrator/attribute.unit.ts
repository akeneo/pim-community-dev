import {hydrator} from 'akeneoenrichedentity/application/hydrator/attribute';

describe('akeneo > enriched entity > application > hydrator --- attribute', () => {
  test('I can hydrate a new attribute', () => {
    const hydrate = hydrator(
      ({identifier, enriched_entity_identifier, code, labels, required, valuePerLocale, valuePerChannel, type}) => {
        expect(identifier).toEqual({identifier: 'description', enriched_entity_identifier: 'designer'});
        expect(code).toEqual('description');
        expect(enriched_entity_identifier).toEqual('designer');
        expect(labels).toEqual({en_US: 'Description'});
      }
    );

    expect(
      hydrate({
        identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
        enriched_entity_identifier: 'designer',
        code: 'description',
        labels: {en_US: 'Description'},
        required: true,
        value_per_locale: false,
        value_per_channel: true,
        type: 'text',
      })
    );
  });

  test('It throw an error if I pass a malformed attribute', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'starck'})).toThrow();
    expect(() => hydrator()({enriched_entity_identifier: 'designer'})).toThrow();
    expect(() => hydrator()({valuePerLocale: false})).toThrow();
  });
});
