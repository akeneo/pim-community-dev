import {useCallback} from 'react';
import {AnyCriterion} from '../models/Criterion';
import AttributeTextCriterion from '../criteria/AttributeTextCriterion';

type Return = (type: string) => AnyCriterion;

export const useFindAttributeCriterionByType = (): Return => {
    return useCallback((type: string): AnyCriterion => {
        switch (type) {
            case 'pim_catalog_text':
                return AttributeTextCriterion;
        }

        throw Error('Unknown attribute type');
    }, []);
};
