import {ConcreteTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {MaxLength} from 'akeneoassetmanager/domain/model/attribute/type/text/max-length';
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
      new ConcreteTextAttribute('description', 'designer', 'description', {en_US: 'Description'}, true, false, 0, true);
    }).toThrow('Attribute expects a MaxLength as maxLength');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        {en_US: 'Description'},
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        false,
        true
      );
    }).toThrow('Attribute cannot be rich text editor and not textarea');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        {en_US: 'Description'},
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        false,
        false
      );
    }).toThrow('Attribute expects a ValidationRule as validationRule');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        {en_US: 'Description'},
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        false,
        false,
        ValidationRule.createFromNormalized('regular_expression')
      );
    }).toThrow('Attribute expects a RegularExpression as regularExpression');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        {en_US: 'Description'},
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        true,
        false,
        ValidationRule.createFromNormalized('regular_expression')
      );
    }).toThrow('Attribute cannot have a validation rule while being a textarea');
    expect(() => {
      new ConcreteTextAttribute(
        'description',
        'designer',
        'description',
        {en_US: 'Description'},
        true,
        false,
        0,
        true,
        MaxLength.createFromNormalized(12),
        true,
        false,
        ValidationRule.createFromNormalized('none'),
        RegularExpression.createFromNormalized('hey')
      );
    }).toThrow(
      'Attribute cannot have a regular expression while the validation rule is not ValidationRuleOption.RegularExpression'
    );
  });
});
