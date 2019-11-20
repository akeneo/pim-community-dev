const CHANGE = 'CHANGE';
interface ChangeAction {
    type: typeof CHANGE;
    name: string;
    value: string;
}
const inputChanged = (name: string, value: string): ChangeAction => ({type: CHANGE, name, value});

const SET_ERROR = 'SET_ERROR';
interface SetErrorAction {
    type: typeof SET_ERROR;
    name: string;
    code: string;
}
const setError = (name: string, code: string): SetErrorAction => ({type: SET_ERROR, name, code});

const VALID_FORM = 'VALID_FORM';
interface ValidFormAction {
    type: typeof VALID_FORM;
}
const formIsValid = (): ValidFormAction => ({type: VALID_FORM});

const INVALID_FORM = 'INVALID_FORM';
interface InvalidFormAction {
    type: typeof INVALID_FORM;
}
const formIsInvalid = (): InvalidFormAction => ({type: INVALID_FORM});

const CODE_GENERATED = 'CODE_GENERATED';
interface CodeGeneratedAction {
    type: typeof CODE_GENERATED;
    value: string;
}
const codeGenerated = (value: string): CodeGeneratedAction => ({type: CODE_GENERATED, value});

export type CreateFormAction =
    | ChangeAction
    | SetErrorAction
    | ValidFormAction
    | InvalidFormAction
    | CodeGeneratedAction;

export {
    CHANGE,
    SET_ERROR,
    VALID_FORM,
    INVALID_FORM,
    CODE_GENERATED,
    inputChanged,
    codeGenerated,
    setError,
    formIsValid,
    formIsInvalid,
};
