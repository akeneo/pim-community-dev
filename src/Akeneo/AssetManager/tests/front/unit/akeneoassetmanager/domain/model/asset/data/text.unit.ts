import {
  textDataFromString,
  textDataStringValue,
  areTextDataEqual,
  isTextData,
} from 'akeneoassetmanager/domain/model/asset/data/text';

let textData = '12';
describe('akeneo > asset family > domain > model > asset > data --- text', () => {
  test('I can create a new TextData with a string', () => {
    expect(textDataFromString(textData)).toEqual(textData);
    expect(textDataFromString('')).toEqual(null);
    expect(textDataFromString('<p></p>\n')).toEqual(null);
  });

  test('I can get the string value of a TextData', () => {
    expect(textDataStringValue(textData)).toEqual(textData);
    expect(textDataStringValue(null)).toEqual('');
  });

  test('I can test if a TextData is equal to another', () => {
    expect(areTextDataEqual(textData, null)).toBe(false);
    expect(areTextDataEqual(null, null)).toBe(true);
    expect(areTextDataEqual(null, textData)).toBe(false);
    expect(areTextDataEqual(textData, textData)).toBe(true);
  });

  test('I can test if something is a TextData', () => {
    expect(isTextData(textData)).toBe(true);
    expect(isTextData(null)).toBe(true);
    expect(isTextData({})).toBe(false);
  });
});
