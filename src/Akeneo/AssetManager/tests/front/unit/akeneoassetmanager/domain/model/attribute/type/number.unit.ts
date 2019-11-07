import {ConcreteNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {MinValue} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';

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

  test('I cannot create an invalid ConcreteNumberAttribute (wrong MinValue)', () => {
    expect(() => {
      new ConcreteNumberAttribute('age', 'designer', 'age', {en_US: 'Age'}, false, false, 0, true, true, 12.12, 13);
    }).toThrow('Attribute expects a MinValue as minValue');
  });

  test('I cannot create an invalid ConcreteNumberAttribute (wrong MaxValue)', () => {
    expect(() => {
      new ConcreteNumberAttribute(
        'age',
        'designer',
        'age',
        {en_US: 'Age'},
        false,
        false,
        0,
        true,
        true,
        new MinValue('12.12'),
        13
      );
    }).toThrow('Attribute expects a MaxValue as maxValue');
  });
});
