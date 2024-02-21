import {validateDelimiter} from '../validateDelimiter';

describe('validateDelimiter', () => {
  it('should not add violation for valid delimiter', () => {
    expect(validateDelimiter('-', 'delimiter')).toHaveLength(0);
  });

  it('should not add violation for no delimiter', () => {
    expect(validateDelimiter(null, 'delimiter')).toHaveLength(0);
  });

  it('should add violation for empty', () => {
    expect(validateDelimiter('', 'delimiter')).toEqual([
      {
        path: 'delimiter',
        message: 'A valid delimiter must be added. If you do not want to have a delimiter, untick the box.',
      },
    ]);
  });

  it('should add violation if delimiter contains comma, semicolon or leading/trailing space', () => {
    expect(validateDelimiter(' /', 'delimiter')).toEqual([
      {
        path: 'delimiter',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);

    expect(validateDelimiter('/ ', 'delimiter')).toEqual([
      {
        path: 'delimiter',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);

    expect(validateDelimiter(',', 'delimiter')).toEqual([
      {
        path: 'delimiter',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);

    expect(validateDelimiter(';', 'delimiter')).toEqual([
      {
        path: 'delimiter',
        message: 'The property must not contain a comma, a semicolon or any space',
      },
    ]);
  });
});
