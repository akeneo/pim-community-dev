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
        message: 'Please add a valid delimiter or untick the box if you don’t want to add a delimiter',
      },
    ]);
  });
});
