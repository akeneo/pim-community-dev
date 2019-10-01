import {ConcreteTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {MaxLength} from 'akeneoassetmanager/domain/model/attribute/type/text/max-length';
import {IsTextarea} from 'akeneoassetmanager/domain/model/attribute/type/text/is-textarea';
import {IsRichTextEditor} from 'akeneoassetmanager/domain/model/attribute/type/text/is-rich-text-editor';
import {ValidationRule} from 'akeneoassetmanager/domain/model/attribute/type/text/validation-rule';
import {RegularExpression} from 'akeneoassetmanager/domain/model/attribute/type/text/regular-expression';

const normalizedDescription = {
  identifier: 'description',
  asset_family_identifier: 'designer',
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
        'description',
        'designer',
        'description',
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true
      );
    }).toThrow('Attribute expects a MaxLength as maxLength');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12)
      );
    }).toThrow('Attribute expects a Textarea as isTextarea');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(false)
      );
    }).toThrow('Attribute expects a IsRichTextEditor as isRichTextEditor');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
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
        'description',
        'designer',
        'description',
        createLabelCollection({en_US: 'Description'}),
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        IsTextarea.createFromNormalized(false),
        IsRichTextEditor.createFromNormalized(false)
      );
    }).toThrow('Attribute expects a ValidationRule as validationRule');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
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
    }).toThrow('Attribute expects a RegularExpression as regularExpression');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
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
        'description',
        'designer',
        'description',
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
