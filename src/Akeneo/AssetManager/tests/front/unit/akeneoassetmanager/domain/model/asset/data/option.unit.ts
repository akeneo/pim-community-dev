import {
  optionDataFromString,
  optionDataStringValue,
  areOptionDataEqual,
  isOptionData,
} from 'akeneoassetmanager/domain/model/asset/data/option';

let optionData = 'coucou';
describe('akeneo > asset family > domain > model > asset > data --- option', () => {
  test('I can create a new OptionData with a string', () => {
    expect(optionDataFromString(optionData)).toEqual(optionData);
    expect(optionDataFromString('')).toEqual(null);
  });

  test('I can get the string value of a OptionData', () => {
    expect(optionDataStringValue(optionData)).toEqual(optionData);
    expect(optionDataStringValue(null)).toEqual('');
  });

  test('I can test if a OptionData is equal to another', () => {
    expect(areOptionDataEqual(optionData, null)).toBe(false);
    expect(areOptionDataEqual(null, null)).toBe(true);
    expect(areOptionDataEqual(null, optionData)).toBe(false);
    expect(areOptionDataEqual(optionData, optionData)).toBe(true);
  });

  test('I can test if something is a OptionData', () => {
    expect(isOptionData(optionData)).toBe(true);
    expect(isOptionData(null)).toBe(true);
    expect(isOptionData({})).toBe(false);
  });
});
