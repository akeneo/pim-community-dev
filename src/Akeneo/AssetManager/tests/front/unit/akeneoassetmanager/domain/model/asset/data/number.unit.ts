import {
  numberDataFromString,
  numberDataStringValue,
  areNumberDataEqual,
  isNumberData,
} from 'akeneoassetmanager/domain/model/asset/data/number';

let numberData = '12';
describe('akeneo > asset family > domain > model > asset > data --- number', () => {
  test('I can create a new NumberData with a string', () => {
    expect(numberDataFromString(numberData)).toEqual(numberData);
    expect(numberDataFromString('')).toEqual(null);
  });

  test('I can get the string value of a NumberData', () => {
    expect(numberDataStringValue(numberData)).toEqual(numberData);
    expect(numberDataStringValue(null)).toEqual('');
  });

  test('I can test if a NumberData is equal to another', () => {
    expect(areNumberDataEqual(numberData, null)).toBe(false);
    expect(areNumberDataEqual(null, null)).toBe(true);
    expect(areNumberDataEqual(null, numberData)).toBe(false);
    expect(areNumberDataEqual(numberData, numberData)).toBe(true);
  });

  test('I can test if something is a NumberData', () => {
    expect(isNumberData(numberData)).toBe(true);
    expect(isNumberData(null)).toBe(true);
    expect(isNumberData({})).toBe(false);
  });
});
