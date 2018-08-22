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
    max_length: null,
    is_textarea: false,
    is_rich_text_editor: false,
    validation_rule: ValidationRuleOption.Email,
    regular_expression: '',
  },
  errors: [],
});

const textAttributeReducer = (
  normalizedAttribute: NormalizedTextAttribute,
  propertyCode: string,
  propertyValue: NormalizedTextAdditionalProperty
): NormalizedTextAttribute => {
  switch (propertyCode) {
    case 'max_length':
      return {...normalizedAttribute, max_length: propertyValue as NormalizedMaxLength};
      break;
    case 'is_textarea':
      const is_textarea = propertyValue as NormalizedIsTextarea;
      return {
        ...normalizedAttribute,
        is_textarea,
        is_rich_text_editor: false === is_textarea ? false : normalizedAttribute.is_rich_text_editor,
        validation_rule: true === is_textarea ? ValidationRuleOption.None : normalizedAttribute.validation_rule,
        regular_expression: true === is_textarea ? null : normalizedAttribute.regular_expression,
      };
      break;
    case 'is_rich_text_editor':
      const is_rich_text_editor = propertyValue as NormalizedIsRichTextEditor;
      if (false === normalizedAttribute.is_textarea) {
        return normalizedAttribute;
      }

      return {
        ...normalizedAttribute,
        is_rich_text_editor,
      };
      break;
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
      break;
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
      break;

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
      return {...normalizedAttribute, max_file_size: max_file_size};
      break;
    case 'allowed_extensions':
      const allowed_extensions = propertyValue as NormalizedAllowedExtensions;
      return {...normalizedAttribute, allowed_extensions};
      break;

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
      break;
    case 'image':
      return imageAttributeReducer(
        normalizedAttribute,
        propertyCode,
        propertyValue as NormalizedImageAdditionalProperty
      );
      break;

    default:
      break;
  }

  return normalizedAttribute;
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
    propertyValue: NormalizedAdditionalProperty;
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
