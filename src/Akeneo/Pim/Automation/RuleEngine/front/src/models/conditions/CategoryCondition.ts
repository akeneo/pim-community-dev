import {ConditionFactory} from './Condition';
import React from 'react';
import {Operator} from '../Operator';
import {CategoryCode} from '../Category';
import {CategoryConditionLine} from '../../pages/EditRules/components/conditions/CategoryConditionLine';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {ConditionLineProps} from '../../pages/EditRules/components/conditions/ConditionLineProps';

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
  module: React.FC<ConditionLineProps>;
  field: string;
  operator: Operator;
  value?: CategoryCode[];
};

const getCategoryConditionModule: ConditionModuleGuesser = async json => {
  if (json.field !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return CategoryConditionLine;
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
  getCategoryConditionModule,
  createCategoryCondition,
  CategoryOperators,
};
