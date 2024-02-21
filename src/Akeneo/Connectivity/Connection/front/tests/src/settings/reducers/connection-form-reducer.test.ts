import {
    codeGenerated,
    formIsInvalid,
    formIsValid,
    inputChanged,
    setError,
} from '@src/settings/actions/create-form-actions';
import {connectionFormReducer, CreateFormState} from '@src/settings/reducers/connection-form-reducer';

describe('Connection form reducer', () => {
    it('handles CHANGE action', () => {
        const initialState: CreateFormState = {
            controls: {},
            valid: false,
        };

        const action = inputChanged('label', 'Franklin');

        const newState = connectionFormReducer(initialState, action);

        expect(newState).toStrictEqual({
            controls: {
                label: {
                    name: 'label',
                    value: 'Franklin',
                    errors: {},
                    dirty: true,
                    valid: true,
                },
            },
            valid: false,
        });
    });

    it('handles CHANGE action for existing control', () => {
        const initialState: CreateFormState = {
            controls: {
                label: {
                    name: 'label',
                    value: '',
                    errors: {
                        valueMissing: null,
                    },
                    dirty: true,
                    valid: false,
                },
            },
            valid: false,
        };

        const action = inputChanged('label', 'Franklin');

        const newState = connectionFormReducer(initialState, action);

        expect(newState).toStrictEqual({
            controls: {
                label: {
                    name: 'label',
                    value: 'Franklin',
                    errors: {},
                    dirty: true,
                    valid: true,
                },
            },
            valid: false,
        });
    });

    it('handles SET_ERROR action', () => {
        const initialState: CreateFormState = {
            controls: {
                label: {
                    name: 'label',
                    value: '',
                    errors: {},
                    dirty: true,
                    valid: true,
                },
            },
            valid: true,
        };

        const action = setError('label', 'valueMissing');

        const newState = connectionFormReducer(initialState, action);

        expect(newState).toStrictEqual({
            controls: {
                label: {
                    name: 'label',
                    value: '',
                    errors: {
                        valueMissing: null,
                    },
                    dirty: true,
                    valid: false,
                },
            },
            valid: false,
        });
    });

    it('handles VALID_FORM action', () => {
        const initialState: CreateFormState = {
            controls: {},
            valid: false,
        };

        const action = formIsValid();

        const newState = connectionFormReducer(initialState, action);

        expect(newState).toStrictEqual({
            controls: {},
            valid: true,
        });
    });

    it('handles INVALID_FORM action', () => {
        const initialState: CreateFormState = {
            controls: {},
            valid: true,
        };

        const action = formIsInvalid();

        const newState = connectionFormReducer(initialState, action);

        expect(newState).toStrictEqual({
            controls: {},
            valid: false,
        });
    });

    it('handles CODE_GENERATED action', () => {
        const initialState: CreateFormState = {
            controls: {
                code: {
                    name: 'code',
                    value: '',
                    errors: {},
                    dirty: false,
                    valid: false,
                },
            },
            valid: false,
        };

        const action = codeGenerated('franklin');

        const newState = connectionFormReducer(initialState, action);

        expect(newState).toStrictEqual({
            controls: {
                code: {
                    name: 'code',
                    value: 'franklin',
                    errors: {},
                    dirty: false,
                    valid: true,
                },
            },
            valid: false,
        });
    });
});
