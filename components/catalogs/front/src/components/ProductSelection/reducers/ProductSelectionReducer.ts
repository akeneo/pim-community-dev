import {ProductSelectionValues} from '../models/ProductSelectionValues';
import {AnyCriterionState} from '../models/Criterion';

const removeKey = <T>(object: {[key: string]: T}, property: string): {[key: string]: T} => {
    /* istanbul ignore next */
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const {[property]: _, ...rest} = object;

    return rest;
};

export type ProductSelectionState = ProductSelectionValues;

export enum ProductSelectionActions {
    INITIALIZE = 'INITIALIZE',
    ADD_CRITERION = 'ADD_CRITERION',
    UPDATE_CRITERION = 'UPDATE_CRITERION',
    REMOVE_CRITERION = 'REMOVE_CRITERION',
}

export type ProductSelectionAction =
    | {type: ProductSelectionActions.INITIALIZE; state: ProductSelectionState}
    | {type: ProductSelectionActions.ADD_CRITERION; id: string; state: AnyCriterionState}
    | {type: ProductSelectionActions.UPDATE_CRITERION; id: string; state: AnyCriterionState}
    | {type: ProductSelectionActions.REMOVE_CRITERION; id: string};

export const ProductSelectionReducer = (
    state: ProductSelectionState,
    action: ProductSelectionAction
): ProductSelectionState => {
    switch (action.type) {
        case ProductSelectionActions.INITIALIZE:
            return action.state;
        case ProductSelectionActions.ADD_CRITERION:
            return {
                ...state,
                [action.id]: action.state,
            };
        case ProductSelectionActions.UPDATE_CRITERION:
            return {
                ...state,
                [action.id]: action.state,
            };
        case ProductSelectionActions.REMOVE_CRITERION:
            return removeKey<AnyCriterionState>(state, action.id);
    }

    /* istanbul ignore next */
    return state;
};
