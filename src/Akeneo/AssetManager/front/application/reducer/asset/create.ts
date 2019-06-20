import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import sanitize from 'akeneoreferenceentity/tools/sanitize';

export interface CreateState {
  active: boolean;
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
  };
  errors: ValidationError[];
}

const initCreateState = (): CreateState => ({
  active: false,
  data: {
    code: '',
    labels: {},
  },
  errors: [],
});

export default (
  state: CreateState = initCreateState(),
  action: {type: string; locale: string; value: string; errors: ValidationError[]}
) => {
  switch (action.type) {
    case 'RECORD_CREATION_START':
      state = {...initCreateState(), active: true};
      break;

    case 'RECORD_CREATION_RECORD_CODE_UPDATED':
      state = {
        ...state,
        data: {...state.data, code: action.value},
      };

      break;

    case 'RECORD_CREATION_LABEL_UPDATED':
      const previousLabel = state.data.labels[action.locale];
      const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
      const code = expectedSanitizedCode === state.data.code ? sanitize(action.value) : state.data.code;

      state = {
        ...state,
        data: {...state.data, labels: {...state.data.labels, [action.locale]: action.value}, code: code},
      };

      break;

    case 'RECORD_CREATION_CANCEL':
    case 'DISMISS':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'RECORD_CREATION_SUBMISSION':
      state = {
        ...state,
        errors: [],
      };
      break;

    case 'RECORD_CREATION_SUCCEEDED':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'RECORD_CREATION_ERROR_OCCURED':
      state = {
        ...state,
        errors: action.errors,
      };
      break;
    default:
  }

  return state;
};
