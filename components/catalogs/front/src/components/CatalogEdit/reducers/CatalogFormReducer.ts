import {CatalogFormValues} from '../models/CatalogFormValues';
import {ProductSelectionValues} from '../../ProductSelection';
import {ProductValueFiltersValues} from '../../ProductValueFilters';
import {ProductMapping} from '../../ProductMapping/models/ProductMapping';

type CatalogFormState = CatalogFormValues;

export enum CatalogFormActions {
    INITIALIZE = 'INITIALIZE',
    SET_ENABLED = 'SET_ENABLED',
    SET_PRODUCT_SELECTION_CRITERIA = 'SET_PRODUCT_SELECTION_CRITERIA',
    SET_PRODUCT_VALUE_FILTERS = 'SET_PRODUCT_VALUE_FILTERS',
    SET_PRODUCT_MAPPING = 'SET_PRODUCT_MAPPING',
}

export type CatalogFormAction =
    | {type: CatalogFormActions.INITIALIZE; state: CatalogFormState}
    | {type: CatalogFormActions.SET_ENABLED; value: boolean}
    | {type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA; value: ProductSelectionValues}
    | {type: CatalogFormActions.SET_PRODUCT_VALUE_FILTERS; value: ProductValueFiltersValues}
    | {type: CatalogFormActions.SET_PRODUCT_MAPPING; value: ProductMapping};

export const CatalogFormReducer = (state: CatalogFormState, action: CatalogFormAction): CatalogFormState => {
    switch (action.type) {
        case CatalogFormActions.INITIALIZE:
            return action.state;
        case CatalogFormActions.SET_ENABLED:
            return {
                ...state,
                enabled: action.value,
            };
        case CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA:
            return {
                ...state,
                product_selection_criteria: action.value,
            };
        case CatalogFormActions.SET_PRODUCT_VALUE_FILTERS:
            return {
                ...state,
                product_value_filters: action.value,
            };
        case CatalogFormActions.SET_PRODUCT_MAPPING:
            return {
                ...state,
                product_mapping: action.value,
            };
    }

    /* istanbul ignore next */
    return state;
};
