import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeBooleanCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

export type AttributeBooleanCriterionState = {
    field: string;
    operator: AttributeBooleanCriterionOperator;
    value: boolean | null;
    locale: string | null;
    scope: string | null;
};

export type AttributeBooleanCriterion = Criterion<AttributeBooleanCriterionState>;
