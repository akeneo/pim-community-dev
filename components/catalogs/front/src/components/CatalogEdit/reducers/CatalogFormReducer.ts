import {CatalogFormValues} from '../models/CatalogFormValues';

type CatalogFormState = CatalogFormValues;

export enum CatalogFormActions {
    INITIALIZE = 'INITIALIZE',
    ENABLE = 'ENABLE',
    DISABLE = 'DISABLE',
}

export type CatalogFormAction =
    | {type: CatalogFormActions.INITIALIZE; state: CatalogFormState}
    | {type: CatalogFormActions.ENABLE}
    | {type: CatalogFormActions.DISABLE};

export const CatalogFormReducer = (state: CatalogFormState, action: CatalogFormAction): CatalogFormState => {
    switch (action.type) {
        case CatalogFormActions.INITIALIZE:
            return action.state;
        case CatalogFormActions.ENABLE:
            return {
                ...state,
                enabled: true,
            };
        case CatalogFormActions.DISABLE:
            return {
                ...state,
                enabled: false,
            };
    }

    /* istanbul ignore next */
    return state;
};
