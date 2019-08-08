import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {wrapNormalizableAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {denormalize as denormalizeImageAttribute} from 'akeneoassetmanager/domain/model/attribute/type/image';
import {ConcreteImageAttribute} from 'akeneoassetmanager/domain/model/attribute/type/image';
import {createIdentifier as denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {createIdentifier as createAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';

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
const frontView = denormalizeImageAttribute({
  identifier: 'front_view_1234',
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
});

describe('akeneo > attribute > domain > model --- attribute', () => {
  test('I can create a new attribute with a identifier and labels', () => {
    expect(description.getIdentifier()).toEqual(denormalizeAttributeIdentifier('description_1234'));
  });

  test('I can compare two attributes', () => {
    expect(description.equals(frontView)).toEqual(false);
    expect(description.equals(description)).toEqual(true);
  });

  test('I cannot create a malformed attribute', () => {
    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('front_view_1234'),
        createAssetFamilyIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0
      );
    }).toThrow('Attribute expects a boolean as isRequired value');

    expect(() => {
      new ConcreteImageAttribute(
        denormalizeAttributeIdentifier('front_view_1234'),
        createAssetFamilyIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false
      );
    }).toThrow('Attribute expects a number as order');

    expect(() => {
      new ConcreteImageAttribute(
        'front_view_1234',
        createAssetFamilyIdentifier('designer'),
        createCode('front_view'),
        createLabelCollection({en_US: 'Front View'}),
        true,
        false,
        0
      );
    }).toThrow('Attribute expects an AttributeIdentifier argument');
  });

  test('I can wrap a functionnal attribute in a normalizable object', () => {
    expect(wrapNormalizableAdditionalProperty('nice').normalize()).toEqual('nice');
  });
});
