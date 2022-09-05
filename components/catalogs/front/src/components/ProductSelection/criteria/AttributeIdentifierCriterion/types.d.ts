import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeIdentifierCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.CONTAINS
    | typeof Operator.DOES_NOT_CONTAIN
    | typeof Operator.STARTS_WITH
    | typeof Operator.IN_LIST
    | typeof Operator.NOT_IN_LIST;

export type AttributeIdentifierCriterionState = {
    field: string;
    operator: AttributeIdentifierCriterionOperator;
    value: string | string[];
    locale: string | null;
    scope: string | null;
};

export type AttributeIdentifierCriterion = Criterion<AttributeIdentifierCriterionState>;
