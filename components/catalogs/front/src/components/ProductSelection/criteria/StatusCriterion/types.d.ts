import {Operator} from '../../models/Operator';
import {Criterion} from '../../models/Criterion';

export type StatusCriterionOperator = typeof Operator.EQUALS | typeof Operator.NOT_EQUAL;

export type StatusCriterionState = {
    field: 'enabled';
    operator: StatusCriterionOperator;
    value: boolean;
};

export type StatusCriterion = Criterion<StatusCriterionState>;
