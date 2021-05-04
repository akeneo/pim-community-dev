import {ValidationError} from '@akeneo-pim-community/shared';
import {NormalizedTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {getAttributeReducer, Reducer} from 'akeneoassetmanager/application/configuration/attribute';

export interface EditState {
  isActive: boolean;
  isDirty: boolean;
  isSaving: boolean;
  originalData: string;
  data: NormalizedAttribute;
  errors: ValidationError[];
}

const initEditState = (): EditState => ({
  isActive: false,
  isDirty: false,
  isSaving: false,
  originalData: '',
  data: {
    identifier: '',
    asset_family_identifier: '',
    code: '',
    labels: {},
    type: 'text',
    order: 0,
    value_per_locale: false,
    value_per_channel: false,
    is_required: false,
    is_read_only: false,
    max_length: null,
    is_textarea: false,
    is_rich_text_editor: false,
    validation_rule: 'none',
    regular_expression: null,
  } as NormalizedTextAttribute,
  errors: [],
});

const isDirty = (state: EditState, newData: NormalizedAttribute) => {
  return state.originalData !== JSON.stringify(newData);
};

export const editReducer = (getAttributeReducer: (normalizedAttribute: NormalizedAttribute) => Reducer) => (
  state: EditState = initEditState(),
  {
    type,
    locale,
    value,
    is_required,
    is_read_only,
    errors,
    propertyCode,
    propertyValue,
    attribute,
    attributes,
  }: {
    type: string;
    locale: string;
    value: string;
    is_required: boolean;
    is_read_only: boolean;
    errors: ValidationError[];
    propertyCode: string;
    propertyValue: any;
    attribute: NormalizedAttribute;
    attributes: NormalizedAttribute[];
  }
) => {
  switch (type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      if (!state.isActive) {
        return state;
      }
      const newAttribute = attributes.find(
        (currentAttribute: NormalizedAttribute) => state.data.identifier === currentAttribute.identifier
      );

      if (undefined === newAttribute) {
        return {
          ...state,
          isDirty: false,
          originalData: '',
        };
      }

      state = {
        ...state,
        data: newAttribute,
        isDirty: false,
        originalData: JSON.stringify(newAttribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_START':
      state = {
        ...state,
        isActive: true,
        data: attribute,
        isDirty: false,
        originalData: JSON.stringify(attribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_LABEL_UPDATED':
      if (state.data.labels[locale] === value || !state.isActive) {
        return state;
      }

      const labelUpdatedAttribute = {...state.data, labels: {...state.data.labels, [locale]: value}};

      state = {
        ...state,
        data: labelUpdatedAttribute,
        isDirty: isDirty(state, labelUpdatedAttribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_IS_REQUIRED_UPDATED':
      if (state.data.is_required === is_required || !state.isActive) {
        return state;
      }

      const isRequiredUpdatedAttribute = {...state.data, is_required};

      state = {
        ...state,
        data: isRequiredUpdatedAttribute,
        isDirty: isDirty(state, isRequiredUpdatedAttribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_IS_READ_ONLY_UPDATED':
      if (state.data.is_read_only === is_read_only || !state.isActive) {
        return state;
      }

      const isReadOnlyUpdatedAttribute = {...state.data, is_read_only};

      state = {
        ...state,
        data: isReadOnlyUpdatedAttribute,
        isDirty: isDirty(state, isReadOnlyUpdatedAttribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED':
      if (!state.isActive) {
        return state;
      }
      const data = getAttributeReducer(state.data)(state.data, propertyCode, propertyValue);

      if (data !== state.data) {
        state = {
          ...state,
          isDirty: isDirty(state, data),
          data,
        };
      }

      break;

    case 'ATTRIBUTE_EDITION_CANCEL':
    case 'DISMISS':
      state = {
        ...state,
        isActive: false,
        isDirty: false,
      };
      break;

    case 'ATTRIBUTE_EDITION_SUBMISSION':
      state = {
        ...state,
        errors: [],
        isSaving: true,
      };
      break;

    case 'ATTRIBUTE_EDITION_SUCCEEDED':
      state = {
        ...state,
        isDirty: false,
        isSaving: false,
      };
      break;

    case 'ATTRIBUTE_EDITION_ERROR_OCCURED':
      state = {
        ...state,
        errors: errors,
        isSaving: false,
      };
      break;
    default:
      break;
  }

  return state;
};

export default editReducer(getAttributeReducer);
