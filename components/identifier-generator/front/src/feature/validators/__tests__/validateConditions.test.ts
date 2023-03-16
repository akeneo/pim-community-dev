import {validateConditions} from '../validateConditions';
import {CONDITION_NAMES, Operator} from '../../models';

describe('validateConditions', () => {
  it('should not add violation for valid conditions', () => {
    expect(
      validateConditions(
        [
          {type: CONDITION_NAMES.ENABLED, value: true},
          {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY},
          {type: CONDITION_NAMES.SIMPLE_SELECT, operator: Operator.EMPTY, attributeCode: 'code'},
          {type: CONDITION_NAMES.MULTI_SELECT, operator: Operator.EMPTY, attributeCode: 'code'},
          {type: CONDITION_NAMES.CATEGORIES, operator: Operator.UNCLASSIFIED},
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
        message:
          'The condition type "unknown" is unknown. ' +
          'Please choose one of the following: enabled, family, simple_select, multi_select, category',
        path: 'conditions[0]',
      },
    ]);
  });
});
