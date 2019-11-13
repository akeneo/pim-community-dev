import {ConcreteImageAttribute} from 'akeneoassetmanager/domain/model/attribute/type/image';

const normalizedFrontView = {
  identifier: 'front_view',
  asset_family_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'image',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_file_size: null,
  allowed_extensions: [],
};

describe('akeneo > attribute > domain > model > attribute > type --- ImageAttribute', () => {
  test('I can create a ConcreteImageAttribute from normalized', () => {
    expect(ConcreteImageAttribute.createFromNormalized(normalizedFrontView).normalize()).toEqual(normalizedFrontView);
  });
});
