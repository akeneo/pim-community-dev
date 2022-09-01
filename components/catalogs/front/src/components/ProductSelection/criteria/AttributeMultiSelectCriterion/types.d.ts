import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeMultiSelectCriterionOperator =
    | typeof Operator.IN_LIST
    | typeof Operator.NOT_IN_LIST
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

export type AttributeMultiSelectCriterionState = {
    field: string;
    operator: AttributeMultiSelectCriterionOperator;
    value: array<string>;
    locale: string | null;
    scope: string | null;
};

export type AttributeMultiSelectCriterion = Criterion<AttributeMultiSelectCriterionState>;
