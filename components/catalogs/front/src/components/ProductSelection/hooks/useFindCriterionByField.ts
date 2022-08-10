import {useCallback} from 'react';
import {AnyCriterion} from '../models/Criterion';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';

type Resolver = (field: string) => Promise<AnyCriterion>;

export const useFindCriterionByField = (): Resolver => {
    return useCallback((field: string): Promise<AnyCriterion> => {
        switch (field) {
            case 'enabled':
                return Promise.resolve(StatusCriterion);
            case 'family':
                return Promise.resolve(FamilyCriterion);
        }

        return Promise.reject();
    }, []);
};
