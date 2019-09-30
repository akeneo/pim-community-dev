import {denormalizeIdentifier, identifiersAreEqual} from 'akeneoassetmanager/domain/model/identifier';

describe('akeneo > asset family > domain > model --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(denormalizeIdentifier('michel')).toBe('michel');
  });

  test('I cannot create a new identifier with a value other than a string', () => {
    expect(() => {
      denormalizeIdentifier(12);
    }).toThrow('Identifier expects a string as parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(identifiersAreEqual(denormalizeIdentifier('michel'), denormalizeIdentifier('didier'))).toBe(false);
    expect(identifiersAreEqual(denormalizeIdentifier('didier'), denormalizeIdentifier('didier'))).toBe(true);
  });
});
