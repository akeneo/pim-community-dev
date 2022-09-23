import {validateDelimiter} from '../validateDelimiter';

describe('StructureValidator', () => {
  it('should not add violation for valid delimiter', () => {
    expect(validateDelimiter('-', 'delimiter')).toHaveLength(0);
  });
});
