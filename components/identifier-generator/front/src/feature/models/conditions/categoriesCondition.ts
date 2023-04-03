import {CONDITION_NAMES} from './conditions';
import {Operator} from './operator';
import {CategoryCode} from '@akeneo-pim-community/shared';

type CategoriesCondition = {
  type: CONDITION_NAMES.CATEGORIES;
  operator:
    | Operator.IN
    | Operator.NOT_IN
    | Operator.IN_CHILDREN_LIST
    | Operator.NOT_IN_CHILDREN_LIST
    | Operator.CLASSIFIED
    | Operator.UNCLASSIFIED;
  value?: CategoryCode[];
} & (
  | {
      type: CONDITION_NAMES.CATEGORIES;
      operator: Operator.IN | Operator.NOT_IN | Operator.IN_CHILDREN_LIST | Operator.NOT_IN_CHILDREN_LIST;
      value: CategoryCode[];
    }
  | {
      type: CONDITION_NAMES.CATEGORIES;
      operator: Operator.CLASSIFIED | Operator.UNCLASSIFIED;
    }
);

const CategoriesOperators: Operator[] = [
  Operator.IN,
  Operator.NOT_IN,
  Operator.IN_CHILDREN_LIST,
  Operator.NOT_IN_CHILDREN_LIST,
  Operator.CLASSIFIED,
  Operator.UNCLASSIFIED,
];

export {CategoriesOperators};
export type {CategoriesCondition};
