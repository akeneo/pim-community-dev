import {validateConditions} from '../ConditionsValidator';

describe('ConditionsValidator', () => {
  it('should not add violation for valid conditions', () => {
    expect(validateConditions([], 'conditions')).toHaveLength(0);
  });
});
