import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type AttributeYesNoCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.IS_EMPTY
    | typeof Operator.IS_NOT_EMPTY;

export type AttributeYesNoCriterionState = {
    field: string;
    operator: AttributeYesNoCriterionOperator;
    value: boolean | null;
    locale: string | null;
    scope: string | null;
};

export type AttributeYesNoCriterion = Criterion<AttributeYesNoCriterionState>;
