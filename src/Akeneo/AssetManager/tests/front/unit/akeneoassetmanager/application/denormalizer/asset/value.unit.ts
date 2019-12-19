import valueDenormalizer from 'akeneoassetmanager/application/denormalizer/asset/value';

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

const descriptionenUS = 'en_US';

describe('akeneo > asset family > application > denormalizer > asset --- value', () => {
  test('I can denormalize a value', () => {
    const denormalizeValue = valueDenormalizer({
      attribute: normalizedDescription,
      channel: null,
      locale: descriptionenUS,
      data: normalizedDescription,
    });
    expect(denormalizeValue).toEqual(denormalizeValue);
  });
});
