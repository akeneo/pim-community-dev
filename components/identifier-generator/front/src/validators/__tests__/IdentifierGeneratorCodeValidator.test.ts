import {validateIdentifierGeneratorCode} from '../IdentifierGeneratorCodeValidator';

describe('IdentifierGeneratorCodeValidator', () => {
  it('should not add violation for valid code', () => {
    expect(validateIdentifierGeneratorCode('aValidCode', 'code')).toHaveLength(0);
  });

  it('should add violation with empty code', () => {
    expect(validateIdentifierGeneratorCode('  ', 'code')).toEqual([
      {path: 'code', message: 'Identifier generator code should not be empty'},
    ]);
  });

  it('should add violation with too long code', () => {
    expect(
      validateIdentifierGeneratorCode(
        'Loremipsumdolorsitametconsecteturadipiscingelitseddoeiusmodtemporincididuntutlaboreetdoloremagnaaliqua',
        'code'
      )
    ).toEqual([{path: 'code', message: 'Identifier generator code max length is 100'}]);
  });
});
