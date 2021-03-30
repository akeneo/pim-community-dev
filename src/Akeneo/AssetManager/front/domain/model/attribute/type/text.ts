import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

export enum ValidationRuleOption {
  Email = 'email',
  RegularExpression = 'regular_expression',
  Url = 'url',
  None = 'none',
}

export type ValidationRule = ValidationRuleOption;
export type MaxLength = number | null;

export const maxLengthStringValue = (maxLength: MaxLength): string =>
  null === maxLength ? '' : maxLength.toString(10);
export const isValidMaxLength = (value: any): value is MaxLength => {
  return (!isNaN(parseInt(value)) && 0 < parseInt(value)) || '' === value;
};
export const createMaxLengthFromString = (maxLength: string): MaxLength =>
  '' === maxLength ? null : parseInt(maxLength);

export type RegularExpression = string | null;
export const createRegularExpressionFromString = (regularExpression: string): RegularExpression =>
  '' === regularExpression ? null : regularExpression;
export const regularExpressionStringValue = (regularExpression: RegularExpression): string =>
  null === regularExpression ? '' : regularExpression;

export type IsRichTextEditor = boolean;
export type IsTextarea = boolean;
export type TextAdditionalProperty = MaxLength | IsTextarea | IsRichTextEditor | ValidationRule | RegularExpression;
export type NormalizedTextAdditionalProperty =
  | MaxLength
  | IsTextarea
  | IsRichTextEditor
  | ValidationRule
  | RegularExpression;

export const TEXT_ATTRIBUTE_TYPE = 'text';

export interface NormalizedTextAttribute extends NormalizedAttribute {
  type: typeof TEXT_ATTRIBUTE_TYPE;
  max_length: MaxLength;
  is_textarea: IsTextarea;
  is_rich_text_editor: IsRichTextEditor;
  validation_rule: ValidationRule;
  regular_expression: RegularExpression;
}

export const isTextAttribute = (textAttribute: NormalizedAttribute): textAttribute is NormalizedTextAttribute =>
  textAttribute.type === TEXT_ATTRIBUTE_TYPE;

export const isTextAreaAttribute = (textAttribute: NormalizedAttribute): textAttribute is NormalizedTextAttribute =>
  isTextAttribute(textAttribute) && textAttribute.is_textarea;

export interface TextAttribute extends Attribute {
  maxLength: MaxLength;
  isTextarea: IsTextarea;
  isRichTextEditor: IsRichTextEditor;
  validationRule: ValidationRule;
  regularExpression: RegularExpression;
  normalize(): NormalizedTextAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteTextAttribute extends ConcreteAttribute implements TextAttribute {
  private constructor(
    identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    is_read_only: boolean,
    readonly maxLength: MaxLength,
    readonly isTextarea: IsTextarea,
    readonly isRichTextEditor: IsRichTextEditor,
    readonly validationRule: ValidationRule,
    readonly regularExpression: RegularExpression
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      TEXT_ATTRIBUTE_TYPE,
      valuePerLocale,
      valuePerChannel,
      order,
      is_required,
      is_read_only
    );

    if (!isTextarea && isRichTextEditor) {
      throw new InvalidArgumentError('Attribute cannot be rich text editor and not textarea');
    }

    if (isTextarea && ValidationRuleOption.None !== validationRule) {
      throw new InvalidArgumentError('Attribute cannot have a validation rule while being a textarea');
    }

    if (null !== regularExpression && ValidationRuleOption.RegularExpression !== validationRule) {
      throw new InvalidArgumentError(
        'Attribute cannot have a regular expression while the validation rule is not ValidationRuleOption.RegularExpression'
      );
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedTextAttribute: NormalizedTextAttribute) {
    return new ConcreteTextAttribute(
      denormalizeAttributeIdentifier(normalizedTextAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedTextAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedTextAttribute.code),
      denormalizeLabelCollection(normalizedTextAttribute.labels),
      normalizedTextAttribute.value_per_locale,
      normalizedTextAttribute.value_per_channel,
      normalizedTextAttribute.order,
      normalizedTextAttribute.is_required,
      normalizedTextAttribute.is_read_only,
      normalizedTextAttribute.max_length,
      normalizedTextAttribute.is_textarea,
      normalizedTextAttribute.is_rich_text_editor,
      normalizedTextAttribute.validation_rule,
      normalizedTextAttribute.regular_expression
    );
  }

  public normalize(): NormalizedTextAttribute {
    return {
      ...super.normalize(),
      type: TEXT_ATTRIBUTE_TYPE,
      max_length: this.maxLength,
      is_textarea: this.isTextarea,
      is_rich_text_editor: this.isRichTextEditor,
      validation_rule: this.validationRule,
      regular_expression: this.regularExpression,
    };
  }
}

export const denormalize = ConcreteTextAttribute.createFromNormalized;
