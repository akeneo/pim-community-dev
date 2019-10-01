import {create, denormalize} from 'akeneoassetmanager/domain/model/asset/data/number';

describe('akeneo > asset family > domain > model > asset > data --- number', () => {
  test('I can create a new NumberData with a string', () => {
    expect(create('155').normalize()).toEqual('155');
  });

  test('I cannot create a new NumberData with a value other than a string', () => {
    expect(() => {
      create(12);
    }).toThrow('NumberData expects a string as parameter to be created');
  });

  test('I can normalize a NumberData', () => {
    expect(denormalize('55').normalize()).toEqual('55');
  });

  test('I can get the string value of a NumberData', () => {
    expect(denormalize('65').stringValue()).toEqual('65');
  });

  test('I can test if a NumberData is empty or not', () => {
    expect(denormalize('800').isEmpty()).toBe(false);
    expect(denormalize('').isEmpty()).toBe(true);
    expect(denormalize('0').isEmpty()).toBe(false);
  });

  test('I can test if two NumberData are equal', () => {
    expect(denormalize('90.9').equals(denormalize('90.90'))).toEqual(false);
    expect(denormalize('99').equals(denormalize('990'))).toEqual(false);
    expect(denormalize('990').equals('99')).toEqual(false);
  });
});
