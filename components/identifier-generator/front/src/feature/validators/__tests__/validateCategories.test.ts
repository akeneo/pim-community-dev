import {CONDITION_NAMES, Operator} from '../../models';
import {validateCategories} from '../validateCategories';

describe('validateCategories', () => {
  it('should add violation when property is unknown', () => {
    expect(
      validateCategories(
        {type: CONDITION_NAMES.CATEGORIES, operator: Operator.CLASSIFIED, unknown: 'bar'},
        'conditions[0]'
      )
    ).toEqual([
      {
        message: 'The following properties are unknown: unknown',
        path: 'conditions[0]',
      },
    ]);
  });

  it('should add violation when operator is unknown', () => {
    expect(validateCategories({type: CONDITION_NAMES.CATEGORIES, operator: 'UNKNOWN'}, 'conditions[0]')).toEqual([
      {
        message:
          'The operator should be one of the following: IN, NOT IN, IN CHILDREN, NOT IN CHILDREN, CLASSIFIED, UNCLASSIFIED',
        path: 'conditions[0].operator',
      },
    ]);
  });

  it('should add violation when value is not present', () => {
    expect(validateCategories({type: CONDITION_NAMES.CATEGORIES, operator: Operator.IN}, 'conditions[0]')).toEqual([
      {
        message: 'The value should be defined',
        path: 'conditions[0].value',
      },
    ]);
  });

  it('should add violation when value is empty', () => {
    expect(
      validateCategories({type: CONDITION_NAMES.CATEGORIES, operator: Operator.IN, value: []}, 'conditions[0]')
    ).toEqual([
      {
        message: 'You should filter with at least one category',
        path: 'conditions[0].value',
      },
    ]);
  });

  it('should add violation when value is present', () => {
    expect(
      validateCategories(
        {type: CONDITION_NAMES.CATEGORIES, operator: Operator.CLASSIFIED, value: ['categoryA']},
        'conditions[0]'
      )
    ).toEqual([
      {
        message: 'The value should not be defined',
        path: 'conditions[0].value',
      },
    ]);
  });
});
