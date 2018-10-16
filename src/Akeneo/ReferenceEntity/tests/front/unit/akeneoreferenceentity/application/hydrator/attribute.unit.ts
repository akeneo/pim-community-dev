import {hydrator} from 'akeneoreferenceentity/application/hydrator/attribute';

describe('akeneo > reference entity > application > hydrator --- attribute', () => {
  test('I can hydrate a new attribute', () => {
    const hydrate = hydrator(
      ({identifier, reference_entity_identifier, code, labels, is_required, valuePerLocale, valuePerChannel, type}) => {
        expect(identifier).toEqual('description_1234');
        expect(code).toEqual('description');
        expect(reference_entity_identifier).toEqual('designer');
        expect(labels).toEqual({en_US: 'Description'});
      }
    );

    expect(
      hydrate({
        identifier: 'description_1234',
        reference_entity_identifier: 'designer',
        code: 'description',
        labels: {en_US: 'Description'},
        is_required: true,
        value_per_locale: false,
        value_per_channel: true,
        type: 'text',
      })
    );
  });

  test('It throw an error if I pass a malformed attribute', () => {
    expect(() => hydrator()({})).toThrow();
    expect(() => hydrator()({labels: {}})).toThrow();
    expect(() => hydrator()({identifier: 'starck_1234'})).toThrow();
    expect(() => hydrator()({reference_entity_identifier: 'designer'})).toThrow();
    expect(() => hydrator()({valuePerLocale: false})).toThrow();
  });
});
