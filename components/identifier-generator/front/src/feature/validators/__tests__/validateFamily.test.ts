import {CONDITION_NAMES, Operator} from '../../models';
import {validateFamily} from '../validateFamily';

describe('validateFamily', () => {
  it('should add violation when property is unknown', () => {
    expect(
      validateFamily({type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY, unknown: 'bar'}, 'conditions[0]')
    ).toEqual([
      {
        message: 'The following properties are unknown: unknown',
        path: 'conditions[0]',
      },
    ]);
  });

  it('should add violation when operator is unknown', () => {
    expect(validateFamily({type: CONDITION_NAMES.FAMILY, operator: 'UNKNOWN'}, 'conditions[0]')).toEqual([
      {
        message: 'The operator should be one of the following: IN, NOT IN, EMPTY, NOT EMPTY',
        path: 'conditions[0].operator',
      },
    ]);
  });

  it('should add violation when value is not present', () => {
    expect(validateFamily({type: CONDITION_NAMES.FAMILY, operator: Operator.IN}, 'conditions[0]')).toEqual([
      {
        message: 'The value should be defined',
        path: 'conditions[0].value',
      },
    ]);
  });

  it('should add violation when value is empty', () => {
    expect(validateFamily({type: CONDITION_NAMES.FAMILY, operator: Operator.IN, value: []}, 'conditions[0]')).toEqual([
      {
        message: 'You should filter with at least one family',
        path: 'conditions[0].value',
      },
    ]);
  });

  it('should add violation when value is present', () => {
    expect(
      validateFamily({type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY, value: ['shirts']}, 'conditions[0]')
    ).toEqual([
      {
        message: 'The value should not be defined',
        path: 'conditions[0].value',
      },
    ]);
  });
});
