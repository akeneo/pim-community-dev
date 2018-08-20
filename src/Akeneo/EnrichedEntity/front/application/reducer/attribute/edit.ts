import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {
  NormalizedAttribute,
  AdditionalProperty,
  AttributeType,
  ValidationRuleOptions,
} from 'akeneoenrichedentity/domain/model/attribute/attribute';

export interface EditState {
  active: boolean;
  data: NormalizedAttribute;
  errors: ValidationError[];
}

const initEditState = (): EditState => ({
  active: false,
  data: {
    identifier: {
      identifier: '',
      enriched_entity_identifier: '',
    },
    enriched_entity_identifier: '',
    code: '',
    labels: {},
    type: 'text',
    order: 0,
    value_per_locale: false,
    value_per_channel: false,
    required: false,
    max_length: 0,
    is_textarea: false,
    is_rich_text_editor: false,
    validation_rule: ValidationRuleOptions.Email,
    regular_expression: '',
  },
  errors: [],
});

const allowedAdditionalData = {
  [AttributeType.Text]: ['max_length', 'is_textarea', 'is_rich_text_editor', 'validation_rule'],
  [AttributeType.Image]: ['max_file_size', 'allowed_extensions'],
};

const additionalPropertyReducer = (
  data: NormalizedAttribute,
  propertyCode: string,
  propertyValue: AdditionalProperty
) => {
  if (allowedAdditionalData[data.type as AttributeType].includes(propertyCode)) {
    return {...data, [propertyCode]: propertyValue};
  }

  return data;
};

export default (
  state: EditState = initEditState(),
  {
    type,
    locale,
    value,
    required,
    errors,
    propertyCode,
    propertyValue,
    attribute,
  }: {
    type: string;
    locale: string;
    value: string;
    required: boolean;
    errors: ValidationError[];
    propertyCode: string;
    propertyValue: AdditionalProperty;
    attribute: NormalizedAttribute;
  }
) => {
  switch (type) {
    case 'ATTRIBUTE_EDITION_START':
      state = {
        ...state,
        active: true,
        data: attribute,
      };
      break;
    case 'ATTRIBUTE_EDITION_LABEL_UPDATED':
      state = {
        ...state,
        data: {...state.data, labels: {...state.data.labels, [locale]: value}},
      };
      break;
    case 'ATTRIBUTE_EDITION_REQUIRED_UPDATED':
      state = {
        ...state,
        data: {...state.data, required: required},
      };
      break;
    case 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED':
      const data = additionalPropertyReducer(state.data, propertyCode, propertyValue);

      if (data !== state.data) {
        state = {
          ...state,
          data,
        };
      }

      break;

    case 'ATTRIBUTE_EDITION_CANCEL':
    case 'DISMISS':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ATTRIBUTE_EDITION_SUBMISSION':
      state = {
        ...state,
        errors: [],
      };
      break;

    case 'ATTRIBUTE_EDITION_SUCCEEDED':
      state = {
        ...state,
        active: false,
      };
      break;

    case 'ATTRIBUTE_EDITION_ERROR_OCCURED':
      state = {
        ...state,
        errors: errors,
      };
      break;
    default:
      break;
  }

  return state;
};
