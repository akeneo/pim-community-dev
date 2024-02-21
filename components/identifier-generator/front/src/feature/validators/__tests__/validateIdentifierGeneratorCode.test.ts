import {validateIdentifierGeneratorCode} from '../validateIdentifierGeneratorCode';

describe('validateIdentifierGeneratorCode', () => {
  it('should not add violation for valid code', () => {
    expect(validateIdentifierGeneratorCode('aValidCode', 'code')).toHaveLength(0);
  });

  it('should add violation with empty code', () => {
    expect(validateIdentifierGeneratorCode('  ', 'code')).toEqual([
      {path: 'code', message: 'The identifier generator code must be filled'},
    ]);
  });

  it('should add violation with too long code', () => {
    expect(validateIdentifierGeneratorCode('a'.repeat(120), 'code')).toEqual([
      {path: 'code', message: 'The identifier generator code is too long: it must be 100 characters or less'},
    ]);
  });
});
