import {createCode} from 'akeneoenrichedentity/domain/model/record/code';

describe('akeneo > enriched entity > domain > model --- code', () => {
  test('I can create a new code with a string value', () => {
    expect(createCode('michel').code).toBe('michel');
  });

  test('I cannot create a new code with a value other than a string', () => {
    expect(() => {
      createCode(12);
    }).toThrow('Code expect a string as parameter to be created');
  });

  test('I can compare two codes', () => {
    expect(createCode('michel').equals(createCode('didier'))).toBe(false);
    expect(createCode('didier').equals(createCode('didier'))).toBe(true);
  });
});
