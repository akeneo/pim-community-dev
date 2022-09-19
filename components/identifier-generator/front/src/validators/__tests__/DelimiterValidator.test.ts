import {validateDelimiter} from '../DelimiterValidator';

describe('StructureValidator', () => {
  it('should not add violation for valid delimiter', () => {
    expect(validateDelimiter('-', 'delimiter')).toHaveLength(0);
  });
});
