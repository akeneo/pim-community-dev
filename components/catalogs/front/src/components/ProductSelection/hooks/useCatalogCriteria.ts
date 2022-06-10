import {Criteria} from '../models/Criteria';
import {Operator} from '../models/Operator';
import StatusCriteria from '../criteria/StatusCriteria';

export const useCatalogCriteria = (_id: string): Criteria[] => {
    return [
        StatusCriteria({
            operator: Operator.EQUALS,
            value: true,
        }),
    ];
};
