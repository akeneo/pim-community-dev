import { ConditionDenormalizer, ConditionFactory } from './Condition';
import { Router } from '../../dependenciesTools';
import React from 'react';
import { Operator } from '../Operator';
import { CategoryCode } from '../Category';
import {
  CategoryConditionLine,
  CategoryConditionLineProps,
} from '../../pages/EditRules/components/conditions/CategoryConditionLine';

const FIELD = 'categories';

const CategoryOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IN_CHILDREN_LIST,
  Operator.NOT_IN_CHILDREN_LIST,
  Operator.UNCLASSIFIED,
  Operator.IN_LIST_OR_UNCLASSIFIED,
];

type CategoryCondition = {
  module: React.FC<CategoryConditionLineProps>;
  field: string;
  operator: Operator;
  value?: CategoryCode[];
};

const denormalizeCategoryCondition: ConditionDenormalizer = async (
  json: any,
  _router: Router
) => {
  if (json.field !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return {
    module: CategoryConditionLine,
    field: FIELD,
    operator: json.operator as Operator,
    value: json.value as CategoryCode[],
  } as CategoryCondition;
};

const createCategoryCondition: ConditionFactory = async (
  fieldCode: any
): Promise<CategoryCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<CategoryCondition>({
    module: CategoryConditionLine,
    field: FIELD,
    operator: CategoryOperators[0],
    value: [],
  });
};

export {
  CategoryCondition,
  denormalizeCategoryCondition,
  createCategoryCondition,
  CategoryOperators,
};
