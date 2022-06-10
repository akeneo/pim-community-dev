import {Operator} from '../../models/Operator';
import {Criteria} from '../../models/Criteria';

export type StatusCriteriaOperator = typeof Operator.EQUALS | typeof Operator.NOT_EQUAL;

export type StatusCriteria = Criteria & {
    operator: StatusCriteriaOperator;
    value: boolean;
};

export type StatusCriteriaState = {
    operator?: StatusCriteriaOperator;
    value?: boolean;
};
