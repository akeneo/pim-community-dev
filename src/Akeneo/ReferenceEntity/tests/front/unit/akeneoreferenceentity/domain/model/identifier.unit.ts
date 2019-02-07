import {createIdentifier} from 'akeneoreferenceentity/domain/model/identifier';

describe('akeneo > reference entity > domain > model --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(createIdentifier('michel').identifier).toBe('michel');
  });

  test('I cannot create a new identifier with a value other than a string', () => {
    expect(() => {
      createIdentifier(12);
    }).toThrow('Identifier expects a string as parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(createIdentifier('michel').equals(createIdentifier('didier'))).toBe(false);
    expect(createIdentifier('didier').equals(createIdentifier('didier'))).toBe(true);
  });
});
