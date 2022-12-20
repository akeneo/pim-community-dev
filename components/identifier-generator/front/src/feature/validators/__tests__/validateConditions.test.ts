import {validateConditions} from '../validateConditions';
import {CONDITION_NAMES, Operator} from '../../models';

describe('validateConditions', () => {
  it('should not add violation for valid conditions', () => {
    expect(
      validateConditions(
        [
          {type: CONDITION_NAMES.ENABLED, value: true},
          {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY, value: undefined},
        ],
        'conditions'
      )
    ).toHaveLength(0);
  });

  it('should add violation for any non valid condition', () => {
    expect(validateConditions([{type: CONDITION_NAMES.ENABLED}], 'conditions')).toEqual([
      {
        message: 'The value should not be undefined',
        path: 'conditions[0].value',
      },
    ]);
  });

  it('should add violation for an unknown type', () => {
    expect(validateConditions([{type: 'unknown'}], 'conditions')).toEqual([
      {
        message: 'The condition type "unknown" is unknown. Please choose one of the following: enabled, family',
        path: 'conditions[0]',
      },
    ]);
  });
});
