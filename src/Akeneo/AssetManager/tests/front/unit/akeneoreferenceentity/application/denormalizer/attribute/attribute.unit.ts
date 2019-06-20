import denormalize, {denormalizeAttribute} from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';

const normalizedDescription = {
  identifier: 'description_1234',
  asset_family_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
};

describe('akeneo > asset family > application > denormalizer > attribute --- attribute', () => {
  test('I can denormalize an attribute', () => {
    expect(denormalizeAttribute(() => denormalizeTextAttribute)(normalizedDescription).normalize()).toEqual(
      normalizedDescription
    );
  });

  test('I can execute the denormalizer', () => {
    expect(() => denormalize(normalizedDescription).normalize()).toThrow();
  });
});
