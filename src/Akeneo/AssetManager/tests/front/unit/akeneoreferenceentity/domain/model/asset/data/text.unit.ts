import {create, denormalize} from 'akeneoreferenceentity/domain/model/record/data/text';

describe('akeneo > reference entity > domain > model > record > data --- text', () => {
  test('I can create a new TextData with a string', () => {
    expect(create('nice value').normalize()).toEqual('nice value');
  });

  test('I cannot create a new TextData with a value other than a string', () => {
    expect(() => {
      create(12);
    }).toThrow('TextData expects a string as parameter to be created');
  });

  test('I can normalize a TextData', () => {
    expect(denormalize('awesome text').normalize()).toEqual('awesome text');
  });

  test('I can get the string value of a TextData', () => {
    expect(denormalize('awesome text').stringValue()).toEqual('awesome text');
  });

  test('I can test if a TextData is empty or not', () => {
    expect(denormalize('awesome text').isEmpty()).toBe(false);
    expect(denormalize('').isEmpty()).toBe(true);
    expect(denormalize('<p></p>\n').isEmpty()).toBe(true);
  });

  test('I can test if two textData are equal', () => {
    expect(denormalize('awesome text').equals(denormalize('awesome text'))).toEqual(true);
    expect(denormalize('awesome text').equals(denormalize('nice text'))).toEqual(false);
    expect(denormalize('awesome text').equals('awesome text')).toEqual(false);
  });
});
