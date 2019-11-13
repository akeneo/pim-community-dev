import {Reducer} from 'react';
import {
    CHANGE,
    SET_ERROR,
    SET_VALIDATED,
    CreateFormAction
} from '../actions/create-form-actions';

export interface CreateFormState {
    controls: {
        [name: string]: {
            name: string;
            value: string;
            errors: {
                [code: string]: null;
            };
            dirty: boolean;
            valid: boolean;
            validated: boolean;
        };
    };
    valid: boolean;
}

export const appFormReducer: Reducer<CreateFormState, CreateFormAction> = (state, action) => {
    switch (action.type) {
        case CHANGE:
            return {
                ...state,
                controls: {
                    ...state.controls,
                    [action.name]: {
                        ...state.controls[action.name],
                        value: action.value,
                        errors: {},
                        dirty: undefined !== action.dirty ? action.dirty : true,
                        valid: true,
                        validated: false,
                    },
                },
                valid:
                    false ===
                    Object.values(state.controls)
                        .filter(({name}) => name !== action.name)
                        .some(({valid}) => false === valid),
            };
        case SET_ERROR:
            return {
                ...state,
                controls: {
                    ...state.controls,
                    [action.name]: {
                        ...state.controls[action.name],
                        errors: {
                            ...state.controls[action.name].errors,
                            [action.code]: null,
                        },
                        valid: false,
                    },
                },
                valid: false,
            };
        case SET_VALIDATED:
            return {
                ...state,
                controls: {
                    ...state.controls,
                    [action.name]: {
                        ...state.controls[action.name],
                        validated: true,
                    },
                },
            };
        default:
            throw new Error();
    }
};
