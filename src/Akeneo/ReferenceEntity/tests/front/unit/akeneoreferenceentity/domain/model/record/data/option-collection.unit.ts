import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/option-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';

describe('akeneo > reference entity > domain > model > record > data --- option collection', () => {
  test('I can create a new OptionData with a OptionCode collection value', () => {
    expect(create([]).normalize()).toEqual([]);
    expect(create([createCode('starck')]).normalize()).toEqual(['starck']);
    expect(create([createCode('starck'), createCode('dyson')]).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I cannot create a new OptionData with a value other than a OptionCode collection', () => {
    expect(() => {
      create(12);
    }).toThrow('OptionCollectionData expect an array of OptionCode as parameter to be created');
    expect(() => {
      create([12]);
    }).toThrow('OptionCollectionData expect an array of OptionCode as parameter to be created');
  });

  test('I can normalize an OptionData', () => {
    expect(denormalize(null).normalize()).toEqual([]);
    expect(denormalize(['starck']).normalize()).toEqual(['starck']);
    expect(denormalize(['starck', 'dyson']).normalize()).toEqual(['starck', 'dyson']);
  });

  test('I can get the string value of an OptionData', () => {
    expect(denormalize(null).stringValue()).toEqual('');
    expect(denormalize([]).stringValue()).toEqual('');
    expect(denormalize(['starck']).stringValue()).toEqual('starck');
    expect(denormalize(['starck', 'dyson']).stringValue()).toEqual('starck, dyson');
  });

  test('I can count the number of options in an OptionData', () => {
    expect(denormalize(null).count()).toBe(0);
    expect(denormalize([]).count()).toBe(0);
    expect(denormalize(['starck']).count()).toBe(1);
    expect(denormalize(['starck', 'dyson']).count()).toBe(2);
  });

  test('I can test if the optionData contains an OptionCode', () => {
    expect(denormalize(null).contains(createCode('red'))).toBe(false);
    expect(denormalize([]).contains(createCode('red'))).toBe(false);
    expect(denormalize(['starck']).contains(createCode('starck'))).toBe(true);
    expect(denormalize(['dyson']).contains(createCode('starck'))).toBe(false);
    expect(denormalize(['starck', 'dyson']).contains(createCode('dyson'))).toBe(true);
  });

  test('I can test if two optionData are equal', () => {
    expect(denormalize(['starck']).equals(denormalize(['starck']))).toEqual(true);
    expect(denormalize(['starck', 'dyson']).equals(denormalize(['starck', 'dyson']))).toEqual(true);
    expect(denormalize(['dyson', 'starck']).equals(denormalize(['starck', 'dyson']))).toEqual(false);
    expect(denormalize(['starck']).equals(denormalize(['dyson']))).toEqual(false);
    expect(denormalize(['starck']).equals(['starck'])).toEqual(false);
    expect(denormalize(null).equals(denormalize(null))).toEqual(true);
    expect(denormalize(null).equals(denormalize(['dyson']))).toEqual(false);
  });

  test('I can test if the option data is empty', () => {
    expect(denormalize(['starck']).isEmpty()).toEqual(false);
    expect(denormalize(null).isEmpty()).toEqual(true);
  });
});
