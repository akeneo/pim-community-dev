import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {wrapNormalizableAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  ConcreteMediaFileAttribute,
  denormalize as denormalizeMediaFileAttribute,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file';

const description = denormalizeTextAttribute({
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
});
const frontView = denormalizeMediaFileAttribute({
  identifier: 'front_view_1234',
  asset_family_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: 'media_file',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_file_size: null,
  allowed_extensions: [],
});

describe('akeneo > attribute > domain > model --- attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getIdentifier()).toEqual('description_1234');
  });

  test('I can compare two attributes', () => {
    expect(description.equals(frontView)).toEqual(false);
    expect(description.equals(description)).toEqual(true);
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new ConcreteMediaFileAttribute(
        'front_view_1234',
        'designer',
        'front_view',
        {en_US: 'Front View'},
        true,
        false,
        0
      );
    }).toThrow('Attribute expects a boolean as isRequired value');

    expect(() => {
      new ConcreteMediaFileAttribute('front_view_1234', 'designer', 'front_view', {en_US: 'Front View'}, true, false);
    }).toThrow('Attribute expects a number as order');
  });

  test('I can wrap a functionnal attribute in a normalizable object', () => {
    expect(wrapNormalizableAdditionalProperty('nice').normalize()).toEqual('nice');
  });
});
