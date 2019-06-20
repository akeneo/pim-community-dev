import {createCode} from 'akeneoreferenceentity/domain/model/record/code';

describe('akeneo > reference entity > domain > model --- code', () => {
  test('I can create a new code with a string value', () => {
    expect(createCode('michel').code).toBe('michel');
  });

  test('I cannot create a new code with a value other than a string', () => {
    expect(() => {
      createCode(12);
    }).toThrow('Code expects a string as parameter to be created');
  });

  test('I can compare two codes', () => {
    expect(createCode('michel').equals(createCode('didier'))).toBe(false);
    expect(createCode('didier').equals(createCode('didier'))).toBe(true);
  });

  test('It has a string representation', () => {
    expect(createCode('michel').stringValue()).toBe('michel');
  });

  test('I normalizes itself', () => {
    expect(createCode('michel').normalize()).toBe('michel');
  });
});
