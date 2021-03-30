import {
  ConcreteTextAttribute,
  maxLengthStringValue,
  isValidMaxLength,
  createMaxLengthFromString,
  createRegularExpressionFromString,
  regularExpressionStringValue,
  isTextAttribute,
  isTextAreaAttribute,
} from 'akeneoassetmanager/domain/model/attribute/type/text';

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
  is_read_only: true,
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

  test('I can denormalize max length', () => {
    expect(maxLengthStringValue(null)).toEqual('');
    expect(maxLengthStringValue(12)).toEqual('12');
  });
  test('I can check if max length is valid', () => {
    expect(isValidMaxLength('')).toEqual(true);
    expect(isValidMaxLength(12)).toEqual(true);
    expect(isValidMaxLength('12')).toEqual(true);
    expect(isValidMaxLength(null)).toEqual(false);
    expect(isValidMaxLength('nice')).toEqual(false);
  });
  test('I can create a max length from string', () => {
    expect(createMaxLengthFromString('')).toEqual(null);
    expect(createMaxLengthFromString('12')).toEqual(12);
  });
  test('I can denormalize regular expression', () => {
    expect(createRegularExpressionFromString('')).toEqual(null);
    expect(createRegularExpressionFromString('/test/')).toEqual('/test/');
  });
  test('I can create a regular expression from string', () => {
    expect(regularExpressionStringValue(null)).toEqual('');
    expect(regularExpressionStringValue('/test/')).toEqual('/test/');
  });

  test('I cannot create an invalid ConcreteTextAttribute', () => {
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
        false,
        12,
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
        false,
        12,
        true,
        false,
        'regular_expression'
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
        false,
        12,
        true,
        false,
        'none',
        'hey'
      );
    }).toThrow(
      'Attribute cannot have a regular expression while the validation rule is not ValidationRuleOption.RegularExpression'
    );
  });

  test('I can check if it is a text attribute', () => {
    expect(isTextAttribute(normalizedDescription)).toBe(true);
    expect(isTextAttribute({...normalizedDescription, type: 'noice'})).toBe(false);
  });

  test('I can check if it is a text area attribute', () => {
    expect(isTextAreaAttribute(normalizedDescription)).toBe(false);
    expect(isTextAreaAttribute({...normalizedDescription, type: 'noice'})).toBe(false);
    expect(isTextAreaAttribute({...normalizedDescription, type: 'noice', is_textarea: true})).toBe(false);
    expect(isTextAreaAttribute({...normalizedDescription, is_textarea: true})).toBe(true);
  });
});
