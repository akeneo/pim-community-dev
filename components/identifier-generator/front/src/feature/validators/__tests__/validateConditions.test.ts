import {validateConditions} from '../validateConditions';

describe('validateConditions', () => {
  it('should not add violation for valid conditions', () => {
    expect(validateConditions([], 'conditions')).toHaveLength(0);
  });
});
