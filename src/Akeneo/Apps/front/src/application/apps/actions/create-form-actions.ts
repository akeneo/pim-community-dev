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

const SET_VALIDATED = 'SET_VALIDATED';

interface SetValidatedAction {
  type: typeof SET_VALIDATED;
  name: string;
}

const setValidated = (name: string): SetValidatedAction => ({type: SET_VALIDATED, name});

export type CreateFormAction = ChangeAction | SetErrorAction | SetValidatedAction;

export {
  CHANGE,
  SET_ERROR,
  SET_VALIDATED,
  inputChanged,
  setError,
  setValidated
};
