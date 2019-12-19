import {ConcreteNumberAttribute, isNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';

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

  test('I can check if it is a number attribute', () => {
    expect(isNumberAttribute(normalizedArea)).toBe(true);
    expect(isNumberAttribute({...normalizedArea, type: 'noice'})).toBe(false);
  });
});
