import {createIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';

describe('akeneo > enriched entity > domain > model --- identifier', () => {
  test('I can create a new identifier with a string value', () => {
    expect(createIdentifier('designer', 'michel').identifier).toBe('michel');
  });

  test('I cannot create a new identifier with a value for enriched entity identifier other than a string', () => {
    expect(() => {
      createIdentifier(12);
    }).toThrow('RecordIdentifier expect a string as first parameter to be created');
  });

  test('I cannot create a new identifier with a value for record identifier other than a string', () => {
    expect(() => {
      createIdentifier('designer', 12);
    }).toThrow('RecordIdentifier expect a string as second parameter to be created');
  });

  test('I can compare two identifiers', () => {
    expect(createIdentifier('designer', 'michel').equals(createIdentifier('designer', 'didier'))).toBe(false);
    expect(createIdentifier('designer', 'didier').equals(createIdentifier('designer', 'didier'))).toBe(true);
  });
});
