import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/option';
import {createCode} from 'akeneoreferenceentity/domain/model/attribute/type/option/option-code';

describe('akeneo > reference entity > domain > model > option > data --- option', () => {
  test('I can create a new OptionData with a OptionCode value', () => {
    expect(create(createCode('red')).normalize()).toEqual('red');
  });

  test('I cannot create a new OptionData with a value other than a OptionCode', () => {
    expect(() => {
      create(12);
    }).toThrow('OptionData expect an OptionCode as parameter to be created');
  });

  test('I can normalize a OptionData', () => {
    expect(denormalize('red').normalize()).toEqual('red');
    expect(denormalize(null).normalize()).toEqual(null);
  });

  test('I can test if an OptionData is empty', () => {
    expect(denormalize('red').isEmpty()).toBe(false);
    expect(denormalize(null).isEmpty()).toBe(true);
  });

  test('I can get the code of an OptionData', () => {
    expect(denormalize('red').getCode()).toEqual(createCode('red'));
    expect(() => denormalize(null).getCode()).toThrow('Cannot get the option code on an empty OptionData');
  });

  test('I can get the string value of an OptionData', () => {
    expect(denormalize('red').stringValue()).toEqual('red');
    expect(denormalize(null).stringValue()).toEqual('');
  });

  test('I can test if two optionData are equal', () => {
    expect(denormalize('red').equals(denormalize('red'))).toEqual(true);
    expect(denormalize('red').equals(denormalize('blue'))).toEqual(false);
    expect(denormalize('red').equals('red')).toEqual(false);
    expect(denormalize(null).equals(denormalize(null))).toEqual(true);
    expect(denormalize(null).equals(denormalize('blue'))).toEqual(false);
  });
});
