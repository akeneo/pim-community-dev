import {validateSimpleSelect} from '../validateSimpleSelect';
import {CONDITION_NAMES, Operator, SimpleSelectCondition} from '../../models';

const commonSimpleSelectCondition: SimpleSelectCondition = {
  type: CONDITION_NAMES.SIMPLE_SELECT,
  label: 'My Attribute',
  scopable: false,
  locale: null,
  localizable: false,
  scope: null,
  attributeCode: 'my_attribute',
  value: [],
  operator: Operator.IN,
};

describe('validateSimpleSelect', () => {
  it('should add a violation when operator is IN or NOT_IN and no value is given', () => {
    expect(validateSimpleSelect(commonSimpleSelectCondition, 'path')).toEqual([
      {message: 'A value is required for the My Attribute attribute', path: 'path'},
    ]);

    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          operator: Operator.NOT_IN,
        },
        'path'
      )
    ).toEqual([{message: 'A value is required for the My Attribute attribute', path: 'path'}]);
  });

  it('should add a violation when attribute is localizable and no locale is given', () => {
    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
          localizable: true,
        },
        'path'
      )
    ).toEqual([{message: 'A locale is required for the My Attribute attribute', path: 'path'}]);
  });

  it('should add a violation when attribute is scopable and no channel is given', () => {
    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
          scopable: true,
        },
        'path'
      )
    ).toEqual([{message: 'A channel is required for the My Attribute attribute', path: 'path'}]);
  });

  it('should not add a violation when attribute is correct', () => {
    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
          localizable: true,
          locale: 'fr_FR',
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
          scopable: true,
          scope: 'ecommerce',
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          value: ['option_a'],
          localizable: true,
          locale: 'fr_FR',
          scopable: true,
          scope: 'ecommerce',
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleSelect(
        {
          ...commonSimpleSelectCondition,
          operator: Operator.EMPTY,
          value: [],
        },
        'path'
      )
    ).toEqual([]);

    expect(
      validateSimpleSelect(
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
