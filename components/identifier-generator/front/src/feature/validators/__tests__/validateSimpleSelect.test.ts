import {validateSimpleOrMultiSelect} from '../validateSimpleOrMultiSelect';
import {CONDITION_NAMES, Operator, SimpleOrMultiSelectCondition} from '../../models';

const commonSimpleSelectCondition: SimpleOrMultiSelectCondition = {
  type: CONDITION_NAMES.SIMPLE_SELECT,
  locale: null,
  scope: null,
  attributeCode: 'my_attribute',
  value: [],
  operator: Operator.IN,
};

describe('validateSimpleSelect', () => {
  it('should add a violation when operator is IN or NOT_IN and no value is given', () => {
    expect(validateSimpleOrMultiSelect(commonSimpleSelectCondition, 'path')).toEqual([
      {message: 'A value is required for the my_attribute attribute', path: 'path'},
    ]);

    expect(
      validateSimpleOrMultiSelect(
        {
          ...commonSimpleSelectCondition,
          operator: Operator.NOT_IN,
        },
        'path'
      )
    ).toEqual([{message: 'A value is required for the my_attribute attribute', path: 'path'}]);
  });

  it('should not add a violation when attribute is correct', () => {
    expect(
      validateSimpleOrMultiSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleOrMultiSelect(
        {
          ...commonSimpleSelectCondition,
          operator: Operator.EMPTY,
          value: [],
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleOrMultiSelect(
        {
          ...commonSimpleSelectCondition,
          operator: Operator.NOT_EMPTY,
          value: [],
        },
        'path'
      )
    ).toEqual([]);
  });
});
