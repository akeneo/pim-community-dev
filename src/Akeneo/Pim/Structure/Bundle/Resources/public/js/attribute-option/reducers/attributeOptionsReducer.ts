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

interface UpdateAttributeOptionAction extends Action {
    payload: {
        option: AttributeOption;
    };
}

const UPDATE_ATTRIBUTE_OPTION = 'UPDATE_ATTRIBUTE_OPTION';
export const updateAttributeOptionAction: ActionCreator<UpdateAttributeOptionAction> = (attributeOption: AttributeOption) => {
    return {
        type: UPDATE_ATTRIBUTE_OPTION,
        payload: {
            option: attributeOption,
        }
    };
};

interface CreateAttributeOptionAction extends Action {
    payload: {
        option: AttributeOption;
    };
}

const CREATE_ATTRIBUTE_OPTION = 'CREATE_ATTRIBUTE_OPTION';
export const createAttributeOptionAction: ActionCreator<CreateAttributeOptionAction> = (attributeOption: AttributeOption) => {
    return {
        type: CREATE_ATTRIBUTE_OPTION,
        payload: {
            option: attributeOption,
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
    case UPDATE_ATTRIBUTE_OPTION: {
        if (previousState === null) {
            return previousState;
        }

        const index = previousState.findIndex((attributeOption: AttributeOption) => attributeOption.id === payload.option.id);

        let newState = [...previousState];
        newState[index] = payload.option;

        return newState;
    }
    case CREATE_ATTRIBUTE_OPTION: {
        if (previousState === null) {
            return previousState;
        }

        return [...previousState, payload.option];
    }
    default:
        return previousState;
    }
};
export default attributeOptionsReducer;
