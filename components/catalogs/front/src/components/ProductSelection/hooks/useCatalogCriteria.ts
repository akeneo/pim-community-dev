import {Operator} from '../models/Operator';
import {Criteria} from '../models/Criteria';
import StatusCriterion from '../criteria/StatusCriterion';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export const useCatalogCriteria = (_id: string): Criteria => {
    return [
        StatusCriterion({
            operator: Operator.EQUALS,
            value: true,
        }),
    ];
};
