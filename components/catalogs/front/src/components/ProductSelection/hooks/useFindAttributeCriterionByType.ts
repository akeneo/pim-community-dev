import {useCallback} from 'react';
import {AnyAttributeCriterion} from '../models/Criterion';
import AttributeIdentifierCriterion from '../criteria/AttributeIdentifierCriterion';
import AttributeTextCriterion from '../criteria/AttributeTextCriterion';
import AttributeTextareaCriterion from '../criteria/AttributeTextareaCriterion';
import AttributeSimpleSelectCriterion from '../criteria/AttributeSimpleSelectCriterion';
import AttributeMultiSelectCriterion from '../criteria/AttributeMultiSelectCriterion';
import AttributeNumberCriterion from '../criteria/AttributeNumberCriterion';
import AttributeMeasurementCriterion from '../criteria/AttributeMeasurementCriterion';
import AttributeBooleanCriterion from '../criteria/AttributeBooleanCriterion';
import AttributeDateCriterion from '../criteria/AttributeDateCriterion';

type Return = (type: string) => AnyAttributeCriterion;

export const useFindAttributeCriterionByType = (): Return => {
    return useCallback((type: string): AnyAttributeCriterion => {
        switch (type) {
            case 'pim_catalog_identifier':
                return AttributeIdentifierCriterion;
            case 'pim_catalog_text':
                return AttributeTextCriterion;
            case 'pim_catalog_textarea':
                return AttributeTextareaCriterion;
            case 'pim_catalog_simpleselect':
                return AttributeSimpleSelectCriterion;
            case 'pim_catalog_multiselect':
                return AttributeMultiSelectCriterion;
            case 'pim_catalog_number':
                return AttributeNumberCriterion;
            case 'pim_catalog_metric':
                return AttributeMeasurementCriterion;
            case 'pim_catalog_boolean':
                return AttributeBooleanCriterion;
            case 'pim_catalog_date':
                return AttributeDateCriterion;
        }

        throw Error('Unknown attribute type');
    }, []);
};
