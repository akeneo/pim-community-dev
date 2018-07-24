import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import sanitize from 'akeneoenrichedentity/tools/sanitize';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/attribute';

export interface CreateState {
  active: boolean;
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
    type: AttributeType;
    valuePerLocale: boolean;
    valuePerChannel: boolean;
  };
  errors: ValidationError[];
}

const initCreateState = (): CreateState => ({
  active: false,
  data: {
    code: '',
    type: AttributeType.Text,
    valuePerLocale: false,
    valuePerChannel: false,
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
    attributeType: AttributeType;
    valuePerLocale: boolean;
    valuePerChannel: boolean;
  }
) => {
  switch (action.type) {
    case 'ATTRIBUTE_CREATION_START':
      state = {...initCreateState(), active: true};
      break;

    case 'ATTRIBUTE_CREATION_RECORD_CODE_UPDATED':
      state = {
        ...state,
        data: {...state.data, code: action.value},
      };

      break;

    case 'ATTRIBUTE_CREATION_TYPE_UPDATED':
      state = {
        ...state,
        data: {...state.data, type: action.attributeType},
      };

      break;

    case 'ATTRIBUTE_CREATION_VALUE_PER_LOCALE_UPDATED':
      state = {
        ...state,
        data: {...state.data, valuePerLocale: action.valuePerLocale},
      };

      break;

    case 'ATTRIBUTE_CREATION_VALUE_PER_CHANNEL_UPDATED':
      state = {
        ...state,
        data: {...state.data, valuePerChannel: action.valuePerChannel},
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
