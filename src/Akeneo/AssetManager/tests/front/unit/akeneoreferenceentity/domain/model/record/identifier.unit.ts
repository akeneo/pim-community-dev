import {createIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';

describe('akeneo > reference entity > domain > model --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(createIdentifier('michel').identifier).toBe('michel');
  });

  test('I can get the string value of an identifier', () => {
    expect(createIdentifier('michel').stringValue()).toBe('michel');
  });

  test('I cannot create a new identifier with a value for reference entity identifier other than a string', () => {
    expect(() => {
      createIdentifier(12);
    }).toThrow('RecordIdentifier expects a string as parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(createIdentifier('michel').equals(createIdentifier('didier'))).toBe(false);
    expect(createIdentifier('didier').equals(createIdentifier('didier'))).toBe(true);
  });
});
