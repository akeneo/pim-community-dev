import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/option-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {denormalize as denormalizeOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';

const designer = denormalizeOptionAttribute({
  identifier: 'designer_1234',
  reference_entity_identifier: 'designer',
  code: 'designer',
  labels: {en_US: 'Designer'},
  type: 'option',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  options: [
    {
      code: 'starck',
      labels: {
        en_US: 'Dyson',
      },
    },
    {
      code: 'dyson',
      labels: {
        en_US: 'Dyson',
      },
    },
  ],
});

describe('akeneo > reference entity > domain > model > record > data --- option collection', () => {
  test('I can create a new OptionData with a OptionCode collection value', () => {
    expect(create([]).normalize()).toEqual([]);
    expect(create([createCode('starck')]).normalize()).toEqual(['starck']);
    expect(create([createCode('starck'), createCode('dyson')]).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I cannot create a new OptionData with a value other than a OptionCode collection', () => {
    expect(() => {
      create(12);
    }).toThrow('OptionCollectionData expects an array of OptionCode as parameter to be created');
    expect(() => {
      create([12]);
    }).toThrow('OptionCollectionData expects an array of OptionCode as parameter to be created');
  });

  test('I can normalize an OptionData', () => {
    expect(denormalize(null, designer).normalize()).toEqual([]);
    expect(denormalize(['starck'], designer).normalize()).toEqual(['starck']);
    expect(denormalize(['starck', 'dyson'], designer).normalize()).toEqual(['starck', 'dyson']);
    expect(denormalize(['red', 'dyson'], designer).normalize()).toEqual(['dyson']);
  });

  test('I can get the string value of an OptionData', () => {
    expect(denormalize(null, designer).stringValue()).toEqual('');
    expect(denormalize([], designer).stringValue()).toEqual('');
    expect(denormalize(['starck'], designer).stringValue()).toEqual('starck');
    expect(denormalize(['starck', 'dyson'], designer).stringValue()).toEqual('starck, dyson');
  });

  test('I can count the number of options in an OptionData', () => {
    expect(denormalize(null, designer).count()).toBe(0);
    expect(denormalize([], designer).count()).toBe(0);
    expect(denormalize(['starck'], designer).count()).toBe(1);
    expect(denormalize(['starck', 'dyson'], designer).count()).toBe(2);
  });

  test('I can test if two optionData are equal', () => {
    expect(denormalize(['starck'], designer).equals(denormalize(['starck'], designer))).toEqual(true);
    expect(denormalize(['starck', 'dyson'], designer).equals(denormalize(['starck', 'dyson'], designer))).toEqual(true);
    expect(denormalize(['dyson', 'starck'], designer).equals(denormalize(['starck', 'dyson'], designer))).toEqual(true);
    expect(denormalize(['starck'], designer).equals(denormalize(['dyson'], designer))).toEqual(false);
    expect(denormalize(null, designer).equals(denormalize(null, designer))).toEqual(true);
    expect(denormalize(null, designer).equals(denormalize(['dyson'], designer))).toEqual(false);
  });

  test('I can test if the option data is empty', () => {
    expect(denormalize(['starck'], designer).isEmpty()).toEqual(false);
    expect(denormalize(null, designer).isEmpty()).toEqual(true);
  });
});
