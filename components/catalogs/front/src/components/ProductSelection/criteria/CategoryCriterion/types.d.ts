import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type CategoryCriterionOperator =
    | typeof Operator.IN_LIST
    | typeof Operator.NOT_IN_LIST
    | typeof Operator.IN_CHILDREN_LIST
    | typeof Operator.NOT_IN_CHILDREN_LIST
    | typeof Operator.UNCLASSIFIED
    | typeof Operator.IN_LIST_OR_UNCLASSIFIED;

export type CategoryCriterionState = {
    field: 'categories';
    operator: CategoryCriterionOperator;
    value: array<string>;
};

export type CategoryCriterion = Criterion<CategoryCriterionState>;
