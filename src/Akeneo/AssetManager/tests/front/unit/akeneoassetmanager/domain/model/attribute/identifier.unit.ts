import {
  denormalizeAttributeIdentifier,
  attributeidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/attribute/identifier';

describe('akeneo > asset family > domain > model > attribute --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(denormalizeAttributeIdentifier('michel')).toBe('michel');
  });

  test('I can create a new identifier from normalization', () => {
    expect(denormalizeAttributeIdentifier('michel')).toBe('michel');
  });

  test('I cannot create a new identifier with a value for attribute identifier other than a string', () => {
    expect(() => {
      denormalizeAttributeIdentifier(12);
    }).toThrow('Identifier expects a string as parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(
      attributeidentifiersAreEqual(denormalizeAttributeIdentifier('michel'), denormalizeAttributeIdentifier('didier'))
    ).toBe(false);
    expect(
      attributeidentifiersAreEqual(denormalizeAttributeIdentifier('didier'), denormalizeAttributeIdentifier('didier'))
    ).toBe(true);
  });

  test('I can stringify an attribute', () => {
    expect(denormalizeAttributeIdentifier('michel')).toEqual('michel');
  });
});
