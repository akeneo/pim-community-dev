import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import {
  CommonNormalizedAttribute,
  CommonAttribute,
  CommonConcreteAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/common';
import {MaxLength, NormalizedMaxLength} from 'akeneoenrichedentity/domain/model/attribute/type/text/max-length';
import {IsTextarea, NormalizedIsTextarea} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-textarea';
import {
  IsRichTextEditor,
  NormalizedIsRichTextEditor,
} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-rich-text-editor';
import {
  ValidationRule,
  NormalizedValidationRule,
  ValidationRuleOption,
} from 'akeneoenrichedentity/domain/model/attribute/type/text/validation-rule';
import {
  RegularExpression,
  NormalizedRegularExpression,
} from 'akeneoenrichedentity/domain/model/attribute/type/text/regular-expression';

export type TextAdditionalProperty = MaxLength | IsTextarea | IsRichTextEditor | ValidationRule | RegularExpression;
export type NormalizedTextAdditionalProperty =
  | NormalizedMaxLength
  | NormalizedIsTextarea
  | NormalizedIsRichTextEditor
  | NormalizedValidationRule
  | NormalizedRegularExpression;

export interface NormalizedTextAttribute extends CommonNormalizedAttribute {
  type: 'text';
  max_length: NormalizedMaxLength;
  is_textarea: NormalizedIsTextarea;
  is_rich_text_editor: NormalizedIsRichTextEditor;
  validation_rule: NormalizedValidationRule;
  regular_expression: NormalizedRegularExpression;
}

export interface TextAttribute extends CommonAttribute {
  maxLength: MaxLength;
  isTextarea: IsTextarea;
  isRichTextEditor: IsRichTextEditor;
  validationRule: ValidationRule;
  regularExpression: RegularExpression;
  normalize(): NormalizedTextAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteTextAttribute extends CommonConcreteAttribute implements TextAttribute {
  private constructor(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    required: boolean,
    readonly maxLength: MaxLength,
    readonly isTextarea: IsTextarea,
    readonly isRichTextEditor: IsRichTextEditor,
    readonly validationRule: ValidationRule,
    readonly regularExpression: RegularExpression
  ) {
    super(
      identifier,
      enrichedEntityIdentifier,
      code,
      labelCollection,
      AttributeType.Text,
      valuePerLocale,
      valuePerChannel,
      order,
      required
    );

    if (!(maxLength instanceof MaxLength)) {
      throw new InvalidArgumentError('Attribute expect a MaxLength as maxLength');
    }

    if (!(isTextarea instanceof IsTextarea)) {
      throw new InvalidArgumentError('Attribute expect a Textarea as isTextarea');
    }

    if (!(isRichTextEditor instanceof IsRichTextEditor)) {
      throw new InvalidArgumentError('Attribute expect a IsRichTextEditor as isRichTextEditor');
    }

    if (false === isTextarea.booleanValue() && true === isRichTextEditor.booleanValue()) {
      throw new InvalidArgumentError('Attribute cannot be rich text editor and not textarea');
    }

    if (!(validationRule instanceof ValidationRule)) {
      throw new InvalidArgumentError('Attribute expect a ValidationRule as validationRule');
    }

    if (true === isTextarea.booleanValue() && ValidationRuleOption.None !== validationRule.stringValue()) {
      throw new InvalidArgumentError('Attribute cannot have a validation rule while being a textarea');
    }

    if (!(regularExpression instanceof RegularExpression)) {
      throw new InvalidArgumentError('Attribute expect a RegularExpression as regularExpression');
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
      createIdentifier(
        normalizedTextAttribute.identifier.enriched_entity_identifier,
        normalizedTextAttribute.identifier.identifier
      ),
      createEnrichedEntityIdentifier(normalizedTextAttribute.enriched_entity_identifier),
      createCode(normalizedTextAttribute.code),
      createLabelCollection(normalizedTextAttribute.labels),
      normalizedTextAttribute.value_per_locale,
      normalizedTextAttribute.value_per_channel,
      normalizedTextAttribute.order,
      normalizedTextAttribute.required,
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
