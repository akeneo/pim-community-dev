import {useCallback} from 'react';
import {AnyAttributeCriterion} from '../models/Criterion';
import AttributeTextCriterion from '../criteria/AttributeTextCriterion';
import AttributeSimpleSelectCriterion from '../criteria/AttributeSimpleSelectCriterion';
import AttributeNumberCriterion from '../criteria/AttributeNumberCriterion';
import AttributeMeasurementCriterion from '../criteria/AttributeMeasurementCriterion';

type Return = (type: string) => AnyAttributeCriterion;

export const useFindAttributeCriterionByType = (): Return => {
    return useCallback((type: string): AnyAttributeCriterion => {
        switch (type) {
            case 'pim_catalog_text':
                return AttributeTextCriterion;
            case 'pim_catalog_simpleselect':
                return AttributeSimpleSelectCriterion;
            case 'pim_catalog_number':
                return AttributeNumberCriterion;
            case 'pim_catalog_metric':
                return AttributeMeasurementCriterion;
        }

        throw Error('Unknown attribute type');
    }, []);
};
