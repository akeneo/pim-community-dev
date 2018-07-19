import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import sanitize from "akeneoenrichedentity/tools/sanitize";

export interface CreateRecordState {
    active: boolean;
    data: {
        recordCode: string;
        labels: {
            [localeCode: string]: string
        }
    };
    errors: ValidationError[];
}

const initCreateState = (): CreateRecordState => ({
    active: false,
    data: {
        recordCode: '',
        labels: {}
    },
    errors: []
});

export default (state: CreateRecordState = initCreateState(), action: { type: string, locale: string, value: string, errors: ValidationError[] }) => {
    switch (action.type) {
        case 'RECORD_CREATION_START':
            state = {
                ...state,
                active: true,
                data: {
                    recordCode: '',
                    labels: {}
                },
                errors: []
            };
            break;

        case 'RECORD_CREATION_RECORD_CODE_UPDATED':
            state = {
                ...state,
                data: {...state.data, recordCode: action.value}
            };

            break;

        case 'RECORD_CREATION_LABEL_UPDATED':
            const previousLabel = state.data.labels[action.locale] ;
            const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
            const code = (expectedSanitizedCode === state.data.recordCode) ? sanitize(action.value) : state.data.recordCode;

            state = {
                ...state,
                data: {...state.data, labels: {...state.data.labels, [action.locale]: action.value}, recordCode: code}
            };

            break;

        case 'RECORD_CREATION_CANCEL':
            state = {
                ...state,
                active: false,
            };
            break;

        case 'RECORD_CREATION_SUBMISSION':
            state = {
                ...state,
                errors: []
            };
            break;

        case 'RECORD_CREATION_SUCCEEDED':
            state = {
                ...state,
                active: false
            };
            break;

        case 'RECORD_CREATION_ERROR_OCCURED':
            state = {
                ...state,
                errors: action.errors
            };
            break;
        default:
    }

    return state;
}
