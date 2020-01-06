import {Reducer} from 'react';
import {
    CHANGE,
    CODE_GENERATED,
    CreateFormAction,
    INVALID_FORM,
    SET_ERROR,
    VALID_FORM,
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
        };
    };
    valid: boolean;
}

export const connectionFormReducer: Reducer<CreateFormState, CreateFormAction> = (state, action) => {
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
                        dirty: true,
                        valid: true,
                    },
                },
            };
        case CODE_GENERATED:
            return {
                ...state,
                controls: {
                    ...state.controls,
                    code: {
                        ...state.controls.code,
                        value: action.value,
                        errors: {},
                        valid: true,
                    },
                },
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
        case VALID_FORM:
            return {
                ...state,
                valid: true,
            };
        case INVALID_FORM:
            return {
                ...state,
                valid: false,
            };
        default:
            throw new Error();
    }
};
