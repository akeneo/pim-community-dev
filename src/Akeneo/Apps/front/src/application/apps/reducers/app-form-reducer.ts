import {Reducer} from 'react';

export interface FormState {
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

interface ChangeAction {
    type: 'CHANGE';
    name: string;
    value: string;
    dirty?: boolean;
}

interface ErrorAction {
    type: 'ERROR';
    name: string;
    code: string;
}

interface SetValidatedAction {
    type: 'SET_VALIDATED';
    name: string;
}

export type Actions = ChangeAction | ErrorAction | SetValidatedAction;

export const appFormReducer: Reducer<FormState, Actions> = (state, action) => {
    switch (action.type) {
        case 'CHANGE':
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
        case 'ERROR':
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
        case 'SET_VALIDATED':
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
