import {Criterion} from '../models/Criterion';
import {Operator} from '../models/Operator';
import StatusCriterion from '../criteria/StatusCriterion';

export const useCatalogCriteria = (_id: string): Criterion<any>[] => {
    return [
        StatusCriterion({
            operator: Operator.EQUALS,
            value: true,
        }),
    ];
};
