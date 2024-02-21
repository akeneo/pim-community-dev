import {validateTarget} from '../validateTarget';

describe('validateTarget', () => {
  it('should not add violation for valid target', () => {
    expect(validateTarget('sku', 'target')).toHaveLength(0);
  });

  it('should add violation with empty code', () => {
    expect(validateTarget('  ', 'target')).toEqual([{path: 'target', message: 'The target must be filled'}]);
  });
});
