import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export interface CreateState {
  active: boolean;
  data: {
    code: string
    labels: {
      [localeCode: string]: string
    }
  };
  errors: ValidationError[];
}

const initCreateState = (): CreateState => ({
  active: false,
  data: {
    code: '',
    labels: {}
  },
  errors: []
});

const sanitize = (value: string) => {
  const regex = /[a-zA-Z0-9_]/;

  return value.split('')
      .map((char: string) => char.match(regex) ? char : '_')
      .join('')
      .toLocaleLowerCase();
};

export default (state: CreateState = initCreateState(), action: { type: string, locale: string, value: string, errors: ValidationError[] }) => {
  switch (action.type) {
    case 'ENRICHED_ENTITY_CREATION_START':
      state = {
        ...state,
        active: true,
        data: {
          code: '',
          labels: {}
        },
        errors: []
      };
      break;

    case 'ENRICHED_ENTITY_CREATION_CODE_UPDATED':
      state = {
        ...state,
        data: {...state.data, 'code': action.value}
      };

      break;

    case 'ENRICHED_ENTITY_CREATION_LABEL_UPDATED':
      const previousLabel = state.data.labels[action.locale] ;
      const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
      const code = (expectedSanitizedCode === state.data.code) ? sanitize(action.value) : state.data.code;

      state = {
        ...state,
        data: {...state.data, labels: {...state.data.labels, [action.locale]: action.value}, code}
      };

      break;

    case 'ENRICHED_ENTITY_CREATION_CANCEL':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ENRICHED_ENTITY_CREATION_SUBMISSION':
      state = {
        ...state,
        errors: []
      };
      break;

    case 'ENRICHED_ENTITY_CREATION_SUCCEEDED':
      state = {
        ...state,
        active: false
      };
      break;

    case 'ENRICHED_ENTITY_CREATION_ERROR_OCCURED':
      state = {
        ...state,
        errors: action.errors
      };
      break;
    default:
  }

  return state;
}
