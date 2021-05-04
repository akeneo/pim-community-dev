import {ValidationError} from '@akeneo-pim-community/shared';
import sanitize from 'akeneoassetmanager/tools/sanitize';

export interface CreateState {
  active: boolean;
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
    type: string;
    value_per_locale: boolean;
    value_per_channel: boolean;
  };
  errors: ValidationError[];
}

const initCreateState = (): CreateState => ({
  active: false,
  data: {
    code: '',
    type: 'text',
    value_per_locale: false,
    value_per_channel: false,
    labels: {},
  },
  errors: [],
});

export default (
  state: CreateState = initCreateState(),
  action: {
    type: string;
    locale: string;
    value: string;
    errors: ValidationError[];
    attribute_type: string;
    value_per_locale: boolean;
    value_per_channel: boolean;
  }
) => {
  switch (action.type) {
    case 'ATTRIBUTE_CREATION_START':
      state = {...initCreateState(), active: true};
      break;

    case 'ATTRIBUTE_CREATION_CODE_UPDATED':
      state = {
        ...state,
        data: {...state.data, code: action.value},
      };
      break;

    case 'ATTRIBUTE_CREATION_TYPE_UPDATED':
      state = {
        ...state,
        data: {
          ...state.data,
          type: action.attribute_type,
        },
      };
      break;

    case 'ATTRIBUTE_CREATION_VALUE_PER_LOCALE_UPDATED':
      state = {
        ...state,
        data: {...state.data, value_per_locale: action.value_per_locale},
      };
      break;

    case 'ATTRIBUTE_CREATION_VALUE_PER_CHANNEL_UPDATED':
      state = {
        ...state,
        data: {...state.data, value_per_channel: action.value_per_channel},
      };
      break;

    case 'ATTRIBUTE_CREATION_LABEL_UPDATED':
      const previousLabel = state.data.labels[action.locale];
      const expectedSanitizedCode = sanitize(undefined === previousLabel ? '' : previousLabel);
      const code = expectedSanitizedCode === state.data.code ? sanitize(action.value) : state.data.code;

      state = {
        ...state,
        data: {...state.data, labels: {...state.data.labels, [action.locale]: action.value}, code: code},
      };
      break;

    case 'ATTRIBUTE_CREATION_CANCEL':
    case 'DISMISS':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ATTRIBUTE_CREATION_SUBMISSION':
      state = {
        ...state,
        errors: [],
      };
      break;

    case 'ATTRIBUTE_CREATION_SUCCEEDED':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ATTRIBUTE_CREATION_ERROR_OCCURED':
      state = {
        ...state,
        errors: action.errors,
      };
      break;
    default:
  }

  return state;
};
