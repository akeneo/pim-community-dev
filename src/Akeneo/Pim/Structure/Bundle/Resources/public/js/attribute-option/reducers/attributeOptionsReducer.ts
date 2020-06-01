import {Action, ActionCreator, Reducer} from 'redux';
import {AttributeOption} from '../model';

interface InitializeAttributeOptionsAction extends Action {
    payload: {
        attributeOptions: AttributeOption[];
    };
}

const INITIALIZE_ATTRIBUTE_OPTIONS = 'INITIALIZE_ATTRIBUTE_OPTIONS';
export const initializeAttributeOptionsAction: ActionCreator<InitializeAttributeOptionsAction> = (attributeOptions: AttributeOption[]) => {
    return {
        type: INITIALIZE_ATTRIBUTE_OPTIONS,
        payload: {
            attributeOptions,
        }
    };
};

const attributeOptionsReducer: Reducer<AttributeOption[] | null> = (previousState = null, {type, payload}) => {
    switch (type) {
    case INITIALIZE_ATTRIBUTE_OPTIONS: {
        return [
            ...payload.attributeOptions
        ];
    }
    default:
        return previousState;
    }
};
export default attributeOptionsReducer;
