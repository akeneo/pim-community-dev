import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {NormalizedAttribute, NormalizedAdditionalProperty} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {
  NormalizedTextAttribute,
  NormalizedTextAdditionalProperty,
} from 'akeneoenrichedentity/domain/model/attribute/type/text';
import {NormalizedRegularExpression} from 'akeneoenrichedentity/domain/model/attribute/type/text/regular-expression';
import {
  NormalizedValidationRule,
  ValidationRuleOption,
} from 'akeneoenrichedentity/domain/model/attribute/type/text/validation-rule';
import {NormalizedIsRichTextEditor} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-rich-text-editor';
import {
  NormalizedImageAttribute,
  NormalizedImageAdditionalProperty,
} from 'akeneoenrichedentity/domain/model/attribute/type/image';
import {NormalizedAllowedExtensions} from 'akeneoenrichedentity/domain/model/attribute/type/image/allowed-extensions';
import {NormalizedMaxFileSize} from 'akeneoenrichedentity/domain/model/attribute/type/image/max-file-size';
import {NormalizedIsTextarea} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-textarea';
import {NormalizedMaxLength} from 'akeneoenrichedentity/domain/model/attribute/type/text/max-length';

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
    enriched_entity_identifier: '',
    code: '',
    labels: {},
    type: 'text',
    order: 0,
    value_per_locale: false,
    value_per_channel: false,
    is_required: false,
    max_length: null,
    is_textarea: false,
    is_rich_text_editor: false,
    validation_rule: 'none',
    regular_expression: null,
  } as NormalizedTextAttribute,
  errors: [],
});

const textAttributeReducer = (
  normalizedAttribute: NormalizedTextAttribute,
  propertyCode: string,
  propertyValue: NormalizedTextAdditionalProperty
): NormalizedTextAttribute => {
  switch (propertyCode) {
    case 'max_length':
      return {
        ...normalizedAttribute,
        max_length: propertyValue as NormalizedMaxLength,
      };
    case 'is_textarea':
      const is_textarea = propertyValue as NormalizedIsTextarea;
      return {
        ...normalizedAttribute,
        is_textarea,
        is_rich_text_editor: false === is_textarea ? false : normalizedAttribute.is_rich_text_editor,
        validation_rule: true === is_textarea ? ValidationRuleOption.None : normalizedAttribute.validation_rule,
        regular_expression: true === is_textarea ? null : normalizedAttribute.regular_expression,
      };
    case 'is_rich_text_editor':
      const is_rich_text_editor = propertyValue as NormalizedIsRichTextEditor;
      if (false === normalizedAttribute.is_textarea) {
        return normalizedAttribute;
      }

      return {
        ...normalizedAttribute,
        is_rich_text_editor,
      };
    case 'validation_rule':
      const validation_rule = propertyValue as NormalizedValidationRule;
      if (true === normalizedAttribute.is_textarea) {
        return normalizedAttribute;
      }

      return {
        ...normalizedAttribute,
        validation_rule,
        regular_expression:
          ValidationRuleOption.RegularExpression !== validation_rule ? null : normalizedAttribute.regular_expression,
      };
    case 'regular_expression':
      const regular_expression = propertyValue as NormalizedRegularExpression;
      if (
        true === normalizedAttribute.is_textarea ||
        ValidationRuleOption.RegularExpression !== normalizedAttribute.validation_rule
      ) {
        return normalizedAttribute;
      }

      return {
        ...normalizedAttribute,
        regular_expression,
      };

    default:
      break;
  }

  return normalizedAttribute;
};

const imageAttributeReducer = (
  normalizedAttribute: NormalizedImageAttribute,
  propertyCode: string,
  propertyValue: NormalizedImageAdditionalProperty
): NormalizedImageAttribute => {
  switch (propertyCode) {
    case 'max_file_size':
      const max_file_size = propertyValue as NormalizedMaxFileSize;
      return {...normalizedAttribute, max_file_size};
    case 'allowed_extensions':
      const allowed_extensions = propertyValue as NormalizedAllowedExtensions;
      return {...normalizedAttribute, allowed_extensions};

    default:
      break;
  }

  return normalizedAttribute;
};

const additionalPropertyReducer = (
  normalizedAttribute: NormalizedAttribute,
  propertyCode: string,
  propertyValue: NormalizedAdditionalProperty
) => {
  switch (normalizedAttribute.type) {
    case 'text':
      return textAttributeReducer(normalizedAttribute, propertyCode, propertyValue as NormalizedTextAdditionalProperty);
    case 'image':
      return imageAttributeReducer(
        normalizedAttribute,
        propertyCode,
        propertyValue as NormalizedImageAdditionalProperty
      );

    default:
      break;
  }

  return normalizedAttribute;
};

const isDirty = (state: EditState, newData: NormalizedAttribute) => {
  return state.originalData !== JSON.stringify(newData);
};

export default (
  state: EditState = initEditState(),
  {
    type,
    locale,
    value,
    is_required,
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
    errors: ValidationError[];
    propertyCode: string;
    propertyValue: NormalizedAdditionalProperty;
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
      if (!state.isActive || state.data.labels[locale] === value) {
        return state;
      }

      const labelUpdatedAttribute = {
        ...state.data,
        labels: {...state.data.labels, [locale]: value},
      };

      state = {
        ...state,
        data: labelUpdatedAttribute,
        isDirty: isDirty(state, labelUpdatedAttribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_IS_REQUIRED_UPDATED':
      if (!state.isActive || state.data.is_required === is_required) {
        return state;
      }

      const isRequiredUpdatedAttribute = {...state.data, is_required};

      state = {
        ...state,
        data: isRequiredUpdatedAttribute,
        isDirty: isDirty(state, isRequiredUpdatedAttribute),
      };
      break;
    case 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED':
      if (!state.isActive) {
        return state;
      }
      const data = additionalPropertyReducer(state.data, propertyCode, propertyValue);

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
