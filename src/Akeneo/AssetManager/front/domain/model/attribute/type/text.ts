import Identifier, {createIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  createIdentifier as createAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {
  NormalizedAttribute,
  Attribute,
  ConcreteAttribute,
} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MaxLength, NormalizedMaxLength} from 'akeneoassetmanager/domain/model/attribute/type/text/max-length';
import {IsTextarea, NormalizedIsTextarea} from 'akeneoassetmanager/domain/model/attribute/type/text/is-textarea';
import {
  IsRichTextEditor,
  NormalizedIsRichTextEditor,
} from 'akeneoassetmanager/domain/model/attribute/type/text/is-rich-text-editor';
import {
  ValidationRule,
  NormalizedValidationRule,
  ValidationRuleOption,
} from 'akeneoassetmanager/domain/model/attribute/type/text/validation-rule';
import {
  RegularExpression,
  NormalizedRegularExpression,
} from 'akeneoassetmanager/domain/model/attribute/type/text/regular-expression';

export type TextAdditionalProperty = MaxLength | IsTextarea | IsRichTextEditor | ValidationRule | RegularExpression;
export type NormalizedTextAdditionalProperty =
  | NormalizedMaxLength
  | NormalizedIsTextarea
  | NormalizedIsRichTextEditor
  | NormalizedValidationRule
  | NormalizedRegularExpression;

export interface NormalizedTextAttribute extends NormalizedAttribute {
  type: 'text';
  max_length: NormalizedMaxLength;
  is_textarea: NormalizedIsTextarea;
  is_rich_text_editor: NormalizedIsRichTextEditor;
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
    identifier: Identifier,
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

    if (!(isTextarea instanceof IsTextarea)) {
      throw new InvalidArgumentError('Attribute expects a Textarea as isTextarea');
    }

    if (!(isRichTextEditor instanceof IsRichTextEditor)) {
      throw new InvalidArgumentError('Attribute expects a IsRichTextEditor as isRichTextEditor');
    }

    if (false === isTextarea.booleanValue() && true === isRichTextEditor.booleanValue()) {
      throw new InvalidArgumentError('Attribute cannot be rich text editor and not textarea');
    }

    if (!(validationRule instanceof ValidationRule)) {
      throw new InvalidArgumentError('Attribute expects a ValidationRule as validationRule');
    }

    if (true === isTextarea.booleanValue() && ValidationRuleOption.None !== validationRule.stringValue()) {
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
      createIdentifier(normalizedTextAttribute.identifier),
      createAssetFamilyIdentifier(normalizedTextAttribute.asset_family_identifier),
      createCode(normalizedTextAttribute.code),
      createLabelCollection(normalizedTextAttribute.labels),
      normalizedTextAttribute.value_per_locale,
      normalizedTextAttribute.value_per_channel,
      normalizedTextAttribute.order,
      normalizedTextAttribute.is_required,
      MaxLength.createFromNormalized(normalizedTextAttribute.max_length),
      IsTextarea.createFromNormalized(normalizedTextAttribute.is_textarea),
      IsRichTextEditor.createFromNormalized(normalizedTextAttribute.is_rich_text_editor),
      ValidationRule.createFromNormalized(normalizedTextAttribute.validation_rule),
      RegularExpression.createFromNormalized(normalizedTextAttribute.regular_expression)
    );
  }

  public normalize(): NormalizedTextAttribute {
    return {
      ...super.normalize(),
      type: 'text',
      max_length: this.maxLength.normalize(),
      is_textarea: this.isTextarea.normalize(),
      is_rich_text_editor: this.isRichTextEditor.normalize(),
      validation_rule: this.validationRule.normalize(),
      regular_expression: this.regularExpression.normalize(),
    };
  }
}

export const denormalize = ConcreteTextAttribute.createFromNormalized;
