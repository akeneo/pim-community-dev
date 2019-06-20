import {ConcreteNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import Identifier, {createIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  createIdentifier as createAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {MinValue} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';
import {DecimalsAllowed} from 'akeneoassetmanager/domain/model/attribute/type/number/decimals-allowed';

const normalizedArea = {
  identifier: 'area_city_fingerprint',
  asset_family_identifier: 'city',
  code: 'area',
  labels: {en_US: 'Area'},
  type: 'number',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  decimals_allowed: false,
  min_value: null,
  max_value: null,
};

describe('akeneo > attribute > domain > model > attribute > type --- NumberAttribute', () => {
  test('I can create a ConcreteNumberAttribute from normalized', () => {
    expect(ConcreteNumberAttribute.createFromNormalized(normalizedArea).normalize()).toEqual(normalizedArea);
  });

  test('I cannot create an invalid ConcreteNumberAttribute (wrong decimalsAllowed)', () => {
    expect(() => {
      new ConcreteNumberAttribute(
        createIdentifier('designer', 'age'),
        createAssetFamilyIdentifier('designer'),
        createCode('age'),
        createLabelCollection({en_US: 'Age'}),
        false,
        false,
        0,
        true,
        false,
        12,
        13
      );
    }).toThrow('Attribute expects a DecimalsAllowed as decimalsAllowed');
  });
  test('I cannot create an invalid ConcreteNumberAttribute (wrong MinValue)', () => {
    expect(() => {
      new ConcreteNumberAttribute(
        createIdentifier('designer', 'age'),
        createAssetFamilyIdentifier('designer'),
        createCode('age'),
        createLabelCollection({en_US: 'Age'}),
        false,
        false,
        0,
        true,
        new DecimalsAllowed(true),
        12.12,
        13
      );
    }).toThrow('Attribute expects a MinValue as minValue');
  });

  test('I cannot create an invalid ConcreteNumberAttribute (wrong MaxValue)', () => {
    expect(() => {
      new ConcreteNumberAttribute(
        createIdentifier('designer', 'age'),
        createAssetFamilyIdentifier('designer'),
        createCode('age'),
        createLabelCollection({en_US: 'Age'}),
        false,
        false,
        0,
        true,
        new DecimalsAllowed(true),
        new MinValue('12.12'),
        13
      );
    }).toThrow('Attribute expects a MaxValue as maxValue');
  });
});
