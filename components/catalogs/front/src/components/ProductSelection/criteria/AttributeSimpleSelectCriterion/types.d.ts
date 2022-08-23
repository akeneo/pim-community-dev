import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeSimpleSelectCriterionOperator =
    | typeof Operator.IN_LIST
    | typeof Operator.NOT_IN_LIST
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

export type AttributeSimpleSelectCriterionState = {
    field: string;
    operator: AttributeSimpleSelectCriterionOperator;
    value: array<string>;
    locale: string | null;
    scope: string | null;
};

export type AttributeSimpleSelectCriterion = Criterion<AttributeSimpleSelectCriterionState>;
