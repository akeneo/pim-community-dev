import {
  optionCollectionDataFromArray,
  optionCollectionDataArrayValue,
  areOptionCollectionDataEqual,
  isOptionCollectionData,
} from 'akeneoassetmanager/domain/model/asset/data/option-collection';

let optionCollectionData = ['coucou', 'salut'];
describe('akeneo > asset family > domain > model > asset > data --- option-collection', () => {
  test('I can create a new OptionCollectionData with a string', () => {
    expect(optionCollectionDataFromArray(optionCollectionData)).toEqual(optionCollectionData);
    expect(optionCollectionDataFromArray([])).toEqual(null);
  });

  test('I can get the string value of a OptionCollectionData', () => {
    expect(optionCollectionDataArrayValue(optionCollectionData)).toEqual(optionCollectionData);
    expect(optionCollectionDataArrayValue(null)).toEqual([]);
  });

  test('I can test if a OptionCollectionData is equal to another', () => {
    expect(areOptionCollectionDataEqual(optionCollectionData, null)).toBe(false);
    expect(areOptionCollectionDataEqual(null, null)).toBe(true);
    expect(areOptionCollectionDataEqual(null, optionCollectionData)).toBe(false);
    expect(areOptionCollectionDataEqual(optionCollectionData, optionCollectionData)).toBe(true);
  });

  test('I can test if something is a OptionCollectionData', () => {
    expect(isOptionCollectionData(optionCollectionData)).toBe(true);
    expect(isOptionCollectionData(null)).toBe(true);
    expect(isOptionCollectionData({})).toBe(false);
  });
});
