import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MaxLength, NormalizedMaxLength} from 'akeneoassetmanager/domain/model/attribute/type/text/max-length';
import {
  ValidationRule,
  NormalizedValidationRule,
  ValidationRuleOption,
} from 'akeneoassetmanager/domain/model/attribute/type/text/validation-rule';
import {
  RegularExpression,
  NormalizedRegularExpression,
} from 'akeneoassetmanager/domain/model/attribute/type/text/regular-expression';

export type IsRichTextEditor = boolean;
export type IsTextarea = boolean;
export type TextAdditionalProperty = MaxLength | IsTextarea | IsRichTextEditor | ValidationRule | RegularExpression;
export type NormalizedTextAdditionalProperty =
  | NormalizedMaxLength
  | IsTextarea
  | IsRichTextEditor
  | NormalizedValidationRule
  | NormalizedRegularExpression;

export interface NormalizedTextAttribute extends NormalizedAttribute {
  type: 'text';
  max_length: NormalizedMaxLength;
  is_textarea: IsTextarea;
  is_rich_text_editor: IsRichTextEditor;
  validation_rule: NormalizedValidationRule;
  regular_expression: NormalizedRegularExpression;
}

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
      'text',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!(maxLength instanceof MaxLength)) {
      throw new InvalidArgumentError('Attribute expects a MaxLength as maxLength');
    }

    if (false === isTextarea && true === isRichTextEditor) {
      throw new InvalidArgumentError('Attribute cannot be rich text editor and not textarea');
    }

    if (!(validationRule instanceof ValidationRule)) {
      throw new InvalidArgumentError('Attribute expects a ValidationRule as validationRule');
    }

    if (true === isTextarea && ValidationRuleOption.None !== validationRule.stringValue()) {
      throw new InvalidArgumentError('Attribute cannot have a validation rule while being a textarea');
    }

    if (!(regularExpression instanceof RegularExpression)) {
      throw new InvalidArgumentError('Attribute expects a RegularExpression as regularExpression');
    }

    if (!regularExpression.isNull() && ValidationRuleOption.RegularExpression !== validationRule.stringValue()) {
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
      MaxLength.createFromNormalized(normalizedTextAttribute.max_length),
      normalizedTextAttribute.is_textarea,
      normalizedTextAttribute.is_rich_text_editor,
      ValidationRule.createFromNormalized(normalizedTextAttribute.validation_rule),
      RegularExpression.createFromNormalized(normalizedTextAttribute.regular_expression)
    );
  }

  public normalize(): NormalizedTextAttribute {
    return {
      ...super.normalize(),
      type: 'text',
      max_length: this.maxLength.normalize(),
      is_textarea: this.isTextarea,
      is_rich_text_editor: this.isRichTextEditor,
      validation_rule: this.validationRule.normalize(),
      regular_expression: this.regularExpression.normalize(),
    };
  }
}

export const denormalize = ConcreteTextAttribute.createFromNormalized;
