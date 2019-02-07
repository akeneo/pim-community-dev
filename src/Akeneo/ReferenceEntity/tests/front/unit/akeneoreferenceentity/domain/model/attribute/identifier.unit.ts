import {createIdentifier, denormalizeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';

describe('akeneo > reference entity > domain > model > attribute --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(createIdentifier('michel').identifier).toBe('michel');
  });

  test('I can create a new identifier from normalization', () => {
    expect(denormalizeIdentifier('michel').identifier).toBe('michel');
  });

  test('I cannot create a new identifier with a value for attribute identifier other than a string', () => {
    expect(() => {
      createIdentifier(12);
    }).toThrow('AttributeIdentifier expects a string as parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(createIdentifier('michel').equals(createIdentifier('didier'))).toBe(false);
    expect(createIdentifier('didier').equals(createIdentifier('didier'))).toBe(true);
  });

  test('I can stringify an attribute', () => {
    expect(createIdentifier('michel').stringValue()).toEqual('michel');
  });
});
