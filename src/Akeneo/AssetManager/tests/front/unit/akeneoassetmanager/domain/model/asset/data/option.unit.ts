import {create, denormalize} from 'akeneoassetmanager/domain/model/asset/data/option';
import {denormalize as denormalizeOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';

const color = denormalizeOptionAttribute({
  identifier: 'option_1234',
  asset_family_identifier: 'designer',
  code: 'option',
  labels: {en_US: 'Option'},
  type: 'option',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  options: [
    {
      code: 'red',
      labels: {
        en_US: 'Red',
      },
    },
    {
      code: 'blue',
      labels: {
        en_US: 'Blue',
      },
    },
  ],
});

describe('akeneo > asset family > domain > model > option > data --- option', () => {
  test('I can create a new OptionData with a OptionCode value', () => {
    expect(create('red').normalize()).toEqual('red');
    expect(create(null).normalize()).toEqual(null);
  });

  test('I cannot create a new OptionData with a value other than a OptionCode', () => {
    expect(() => {
      create(12);
    }).toThrow('OptionData expects an OptionCode as parameter to be created');
  });

  test('I can normalize a OptionData', () => {
    expect(denormalize('red', color).normalize()).toEqual('red');
    expect(denormalize('starck', color).normalize()).toEqual(null);
    expect(denormalize(null, color).normalize()).toEqual(null);
  });

  test('I can test if an OptionData is empty', () => {
    expect(denormalize('red', color).isEmpty()).toBe(false);
    expect(denormalize(null, color).isEmpty()).toBe(true);
  });

  test('I can get the code of an OptionData', () => {
    expect(denormalize('red', color).getCode()).toEqual('red');
    expect(() => denormalize(null, color).getCode()).toThrow('Cannot get the option code on an empty OptionData');
  });

  test('I can get the string value of an OptionData', () => {
    expect(denormalize('red', color).stringValue()).toEqual('red');
    expect(denormalize(null, color).stringValue()).toEqual('');
  });

  test('I can test if two optionData are equal', () => {
    expect(denormalize('red', color).equals(denormalize('red', color))).toEqual(true);
    expect(denormalize('red', color).equals(denormalize('blue', color))).toEqual(false);
    expect(denormalize('red', color).equals('red', color)).toEqual(false);
    expect(denormalize(null, color).equals(denormalize(null, color))).toEqual(true);
    expect(denormalize(null, color).equals(denormalize('blue', color))).toEqual(false);
  });
});
