const CHANGE = 'CHANGE';
interface ChangeAction {
  type: typeof CHANGE;
  name: string;
  value: string;
  dirty?: boolean;
}
const inputChanged = (name: string, value: string, dirty?: boolean): ChangeAction => (
  {type: CHANGE, name, value, dirty}
);

const SET_ERROR = 'SET_ERROR';
interface SetErrorAction {
  type: typeof SET_ERROR;
  name: string;
  code: string;
}
const setError = (name: string, code: string): SetErrorAction => ({type: SET_ERROR, name, code});

const VALID_FORM = 'VALID_FORM';
interface ValidFormAction { type: typeof VALID_FORM }
const formIsValid = (): ValidFormAction => ({ type: VALID_FORM });

const INVALID_FORM = 'INVALID_FORM';
interface InvalidFormAction { type: typeof INVALID_FORM }
const formIsInvalid = (): InvalidFormAction => ({ type: INVALID_FORM });


export type CreateFormAction = ChangeAction | SetErrorAction | ValidFormAction | InvalidFormAction;

export {
  CHANGE,
  SET_ERROR,
  VALID_FORM,
  INVALID_FORM,
  inputChanged,
  setError,
  formIsValid,
  formIsInvalid
};
