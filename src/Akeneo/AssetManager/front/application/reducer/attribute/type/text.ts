import {
  IsTextarea,
  MaxLength,
  ValidationRule,
  RegularExpression,
  ValidationRuleOption,
} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {
  NormalizedTextAttribute,
  NormalizedTextAdditionalProperty,
  IsRichTextEditor,
} from 'akeneoassetmanager/domain/model/attribute/type/text';

const textAttributeReducer = (
  normalizedAttribute: NormalizedTextAttribute,
  propertyCode: string,
  propertyValue: NormalizedTextAdditionalProperty
): NormalizedTextAttribute => {
  switch (propertyCode) {
    case 'max_length':
      return {...normalizedAttribute, max_length: propertyValue as MaxLength};
    case 'is_textarea':
      const is_textarea = propertyValue as IsTextarea;
      return {
        ...normalizedAttribute,
        is_textarea,
        is_rich_text_editor: false === is_textarea ? false : normalizedAttribute.is_rich_text_editor,
        validation_rule: true === is_textarea ? ValidationRuleOption.None : normalizedAttribute.validation_rule,
        regular_expression: true === is_textarea ? null : normalizedAttribute.regular_expression,
      };
    case 'is_rich_text_editor':
      const is_rich_text_editor = propertyValue as IsRichTextEditor;
      if (false === normalizedAttribute.is_textarea) {
        return normalizedAttribute;
      }

      return {
        ...normalizedAttribute,
        is_rich_text_editor,
      };
    case 'validation_rule':
      const validation_rule = propertyValue as ValidationRule;
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
      const regular_expression = propertyValue as RegularExpression;
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

export const reducer = textAttributeReducer;
