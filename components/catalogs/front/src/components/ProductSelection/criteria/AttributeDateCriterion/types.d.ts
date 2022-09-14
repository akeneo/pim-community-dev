import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeDateCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.LOWER_THAN
    | typeof Operator.GREATER_THAN
    | typeof Operator.BETWEEN
    | typeof Operator.NOT_BETWEEN
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

export type AttributeDateCriterionState = {
    field: string;
    operator: AttributeDateCriterionOperator;
    value: string | string[] | null;
    locale: string | null;
    scope: string | null;
};

export type AttributeDateCriterion = Criterion<AttributeDateCriterionState>;
