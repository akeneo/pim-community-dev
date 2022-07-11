import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type FamilyCriterionOperator =
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY
    | typeof Operator.IN_LIST
    | typeof Operator.NOT_IN_LIST;

export type FamilyCriterionState = {
    field: 'family';
    operator: FamilyCriterionOperator;
    value: array<string>;
};

export type FamilyCriterion = Criterion<FamilyCriterionState>;
