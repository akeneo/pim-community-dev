import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type StatusCriterionOperator = typeof Operator.EQUALS | typeof Operator.NOT_EQUAL;

export type StatusCriterion = Criterion & {
    operator: StatusCriterionOperator;
    value: boolean;
};

export type StatusCriterionState = {
    operator?: StatusCriterionOperator;
    value?: boolean;
};
