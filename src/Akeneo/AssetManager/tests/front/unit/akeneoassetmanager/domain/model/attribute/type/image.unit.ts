import {ConcreteImageAttribute} from 'akeneoassetmanager/domain/model/attribute/type/image';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {MaxFileSize} from 'akeneoassetmanager/domain/model/attribute/type/image/max-file-size';

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
  test('I cannot create an invalid ConcreteImageAttribute', () => {
    expect(() => {
      new ConcreteImageAttribute(
        'front_view',
        'designer',
        'front_view',
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expects a MaxFileSize as maxFileSize');

    expect(() => {
      new ConcreteImageAttribute(
        'front_view',
        'designer',
        'front_view',
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0,
        true,
        MaxFileSize.createFromNormalized('12.4')
      );
    }).toThrow('Attribute expects a AllowedExtension as allowedExtension');
  });
});
