import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeNumberCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.LOWER_THAN
    | typeof Operator.LOWER_OR_EQUAL_THAN
    | typeof Operator.GREATER_THAN
    | typeof Operator.GREATER_OR_EQUAL_THAN
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

export type AttributeNumberCriterionState = {
    field: string;
    operator: AttributeNumberCriterionOperator;
    value: number | string | null;
    locale: string | null;
    scope: string | null;
};

export type AttributeNumberCriterion = Criterion<AttributeNumberCriterionState>;
