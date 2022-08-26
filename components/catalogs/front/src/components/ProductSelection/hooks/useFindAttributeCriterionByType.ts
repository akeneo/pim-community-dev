import {useCallback} from 'react';
import {AnyAttributeCriterion} from '../models/Criterion';
import AttributeTextCriterion from '../criteria/AttributeTextCriterion';
import AttributeBooleanCriterion from '../criteria/AttributeBooleanCriterion';

type Return = (type: string) => AnyAttributeCriterion;

export const useFindAttributeCriterionByType = (): Return => {
    return useCallback((type: string): AnyAttributeCriterion => {
        switch (type) {
            case 'pim_catalog_text':
                return AttributeTextCriterion;
            case 'pim_catalog_boolean':
                return AttributeBooleanCriterion;
        }

        throw Error('Unknown attribute type');
    }, []);
};
