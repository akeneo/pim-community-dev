import {CatalogFormValues} from '../models/CatalogFormValues';
import {ProductSelectionValues} from '../../ProductSelection';

type CatalogFormState = CatalogFormValues;

export enum CatalogFormActions {
    INITIALIZE = 'INITIALIZE',
    SET_ENABLED = 'SET_ENABLED',
    SET_PRODUCT_SELECTION_CRITERIA = 'SET_PRODUCT_SELECTION_CRITERIA',
}

export type CatalogFormAction =
    | {type: CatalogFormActions.INITIALIZE; state: CatalogFormState}
    | {type: CatalogFormActions.SET_ENABLED; value: boolean}
    | {type: CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA; value: ProductSelectionValues};

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
    }

    /* istanbul ignore next */
    return state;
};
