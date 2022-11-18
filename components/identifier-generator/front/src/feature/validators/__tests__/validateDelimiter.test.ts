import {validateDelimiter} from '../validateDelimiter';

describe('validateDelimiter', () => {
  it('should not add violation for valid delimiter', () => {
    expect(validateDelimiter('-', 'delimiter')).toHaveLength(0);
  });
});
