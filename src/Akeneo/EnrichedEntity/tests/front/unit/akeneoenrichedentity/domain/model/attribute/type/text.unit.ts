import {ConcreteTextAttribute} from 'akeneoenrichedentity/domain/model/attribute/type/text';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoenrichedentity/domain/model/attribute/code';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import {MaxLength} from 'akeneoenrichedentity/domain/model/attribute/type/text/max-length';
import {IsTextarea} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-textarea';
import {IsRichTextEditor} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-rich-text-editor';
import {ValidationRule} from 'akeneoenrichedentity/domain/model/attribute/type/text/validation-rule';
import {RegularExpression} from 'akeneoenrichedentity/domain/model/attribute/type/text/regular-expression';

const normalizedDescription = {
  identifier: 'description',
  enriched_entity_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
};

describe('akeneo > attribute > domain > model > attribute > type --- TextAttribute', () => {
  test('I can create a ConcreteTextAttribute from normalized', () => {
    expect(ConcreteTextAttribute.createFromNormalized(normalizedDescription).normalize()).toEqual(
      normalizedDescription
    );
  });

  test('I cannot create an invalid ConcreteTextAttribute', () => {
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expect a MaxLength as maxLength');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12)
      );
    }).toThrow('Attribute expect a Textarea as isTextarea');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(false)
      );
    }).toThrow('Attribute expect a IsRichTextEditor as isRichTextEditor');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(false),
        IsRichTextEditor.createFromNormalized(true)
      );
    }).toThrow('Attribute cannot be rich text editor and not textarea');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(false),
        IsRichTextEditor.createFromNormalized(false)
      );
    }).toThrow('Attribute expect a ValidationRule as validationRule');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(false),
        IsRichTextEditor.createFromNormalized(false),
        ValidationRule.createFromNormalized('regular_expression')
      );
    }).toThrow('Attribute expect a RegularExpression as regularExpression');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(true),
        IsRichTextEditor.createFromNormalized(false),
        ValidationRule.createFromNormalized('regular_expression')
      );
    }).toThrow('Attribute cannot have a validation rule while being a textarea');
    expect(() => {
      new ConcreteTextAttribute(
        createIdentifier('designer', 'description'),
        createEnrichedEntityIdentifier('designer'),
        createCode('description'),
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(true),
        IsRichTextEditor.createFromNormalized(false),
        ValidationRule.createFromNormalized('none'),
        RegularExpression.createFromNormalized('hey')
      );
    }).toThrow(
      'Attribute cannot have a regular expression while the validation rule is not ValidationRuleOption.RegularExpression'
    );
  });
});
