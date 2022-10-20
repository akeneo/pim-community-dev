import {validateConditions} from '../validateConditions';

describe('ConditionsValidator', () => {
  it('should not add violation for valid conditions', () => {
    expect(validateConditions([], 'conditions')).toHaveLength(0);
  });
});
