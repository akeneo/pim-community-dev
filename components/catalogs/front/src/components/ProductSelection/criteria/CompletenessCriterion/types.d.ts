import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type CompletenessCriterionOperator =
    | typeof Operator.EQUALS
    | typeof Operator.NOT_EQUAL
    | typeof Operator.LOWER_THAN
    | typeof Operator.GREATER_THAN;

export type CompletenessCriterionState = {
    field: 'completeness';
    operator: CompletenessCriterionOperator;
    value: number;
    locale: string | null;
    scope: string | null;
};

export type CompletenessCriterion = Criterion<CompletenessCriterionState>;
