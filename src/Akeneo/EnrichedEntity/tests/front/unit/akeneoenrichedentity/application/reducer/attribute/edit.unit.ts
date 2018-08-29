import reducer from 'akeneoenrichedentity/application/reducer/attribute/edit';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {ValidationRuleOption} from 'akeneoenrichedentity/domain/model/attribute/type/text/validation-rule';

const normalizedDescription = {
  identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
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

describe('akeneo > enriched entity > application > reducer > attribute --- edit', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  const newState = reducer(undefined, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toEqual({
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
      is_required: false,
      max_length: null,
      is_textarea: false,
      is_rich_text_editor: false,
      validation_rule: ValidationRuleOption.Email,
      regular_expression: '',
    },
    isDirty: false,
    isSaving: false,
    errors: [],
    originalData: '',
  });

  test('I can start the edition of an attribute', () => {
    const state = {
      active: false,
      data: {},
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_START',
      attribute: normalizedDescription,
    });

    expect(newState).toEqual({
      active: true,
      data: normalizedDescription,
      isDirty: false,
      originalData: JSON.stringify(normalizedDescription),
      errors: [],
    });
  });

  test('the list of attributes can be updated without effect', () => {
    const state = {
      active: false,
      data: {},
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_UPDATED',
      attribute: normalizedDescription,
    });

    expect(newState).toEqual({
      active: false,
      data: {},
      errors: [],
    });
  });

  test('the list of attributes can be updated', () => {
    const newAttribute = {...normalizedDescription, labels: {en_US: 'new description'}};
    const state = {
      active: true,
      data: normalizedDescription,
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_UPDATED',
      attributes: [newAttribute],
    });

    expect(newState).toEqual({
      active: true,
      data: newAttribute,
      originalData: JSON.stringify(newAttribute),
      isDirty: false,
      errors: [],
    });
  });

  test('the list of attributes does not have any effect', () => {
    const newAttribute = {...normalizedDescription, identifier: {identifier: 'new_description'}};
    const state = {
      active: true,
      data: normalizedDescription,
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_UPDATED',
      attributes: [newAttribute],
    });

    expect(newState).toEqual({
      active: true,
      data: normalizedDescription,
      isDirty: false,
      originalData: '',
      errors: [],
    });
  });

  test('I can update the label of the attribute', () => {
    const state = {
      active: true,
      data: {
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_LABEL_UPDATED',
      value: 'label testé-/$',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        labels: {
          en_US: 'label testé-/$',
        },
      },
      isDirty: true,
      errors: [],
    });
  });

  test("It doesn't update the label if it's the same", () => {
    const state = {
      active: true,
      data: {
        labels: {
          en_US: 'nice',
        },
      },
      isDirty: false,
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_LABEL_UPDATED',
      value: 'nice',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        labels: {
          en_US: 'nice',
        },
      },
      isDirty: false,
      errors: [],
    });
  });

  test("It doesn't update the is required if it's the same", () => {
    const state = {
      active: true,
      data: {
        is_required: false,
      },
      isDirty: false,
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_IS_REQUIRED_UPDATED',
      is_required: false,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        is_required: false,
      },
      isDirty: false,
      errors: [],
    });
  });

  test('I can update the is_required property of the attribute', () => {
    const state = {
      active: true,
      data: {
        is_required: true,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_IS_REQUIRED_UPDATED',
      is_required: false,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        is_required: false,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the is_required property of the attribute', () => {
    const state = {
      active: true,
      data: {
        is_required: true,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_IS_REQUIRED_UPDATED',
      is_required: false,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        is_required: false,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the textarea property of the attribute', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_rich_text_editor: false,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'is_textarea',
      propertyValue: true,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: true,
        is_rich_text_editor: false,
        regular_expression: null,
        validation_rule: 'none',
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the textarea property of the attribute to false', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_rich_text_editor: false,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'is_textarea',
      propertyValue: true,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: true,
        is_rich_text_editor: false,
        regular_expression: null,
        validation_rule: 'none',
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the textarea property of the attribute to true', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_rich_text_editor: true,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'is_textarea',
      propertyValue: false,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        is_rich_text_editor: false,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the is rich text editor property of the attribute to true', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_rich_text_editor: false,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'is_rich_text_editor',
      propertyValue: true,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_rich_text_editor: true,
      },
      isDirty: true,
      errors: [],
    });
  });

  test("I can't update the is rich text editor property of the attribute to true without textarea", () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        is_rich_text_editor: false,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'is_rich_text_editor',
      propertyValue: true,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        is_rich_text_editor: false,
      },
      errors: [],
    });
  });

  test('I can update the max length property of the attribute', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        max_length: null,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'max_length',
      propertyValue: 120,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        max_length: 120,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the validation rule property of the attribute to none', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'validation_rule',
      propertyValue: 'none',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        validation_rule: 'none',
        regular_expression: null,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the validation rule property of the attribute to regular expression', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'validation_rule',
      propertyValue: 'regular_expression',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        validation_rule: 'regular_expression',
      },
      isDirty: true,
      errors: [],
    });
  });

  test("I can't update the validation rule property of the attribute to regular expression", () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_textarea: true,
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'validation_rule',
      propertyValue: 'regular_expression',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: true,
      },
      errors: [],
    });
  });

  test('I can update the regular expression property of the attribute', () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        validation_rule: 'regular_expression',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'regular_expression',
      propertyValue: 'hey!',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        validation_rule: 'regular_expression',
        regular_expression: 'hey!',
      },
      isDirty: true,
      errors: [],
    });
  });

  test("I can't update the regular expression property of the attribute", () => {
    const state = {
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        validation_rule: 'none',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'regular_expression',
      propertyValue: 'hey!',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
        is_textarea: false,
        validation_rule: 'none',
      },
      errors: [],
    });
  });

  test("I can't update the max file size property of the attribute", () => {
    const state = {
      active: true,
      data: {
        type: 'text',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'max_file_size',
      propertyValue: '12.3',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'text',
      },
      errors: [],
    });
  });

  test('I can update the max file size property of the attribute', () => {
    const state = {
      active: true,
      data: {
        type: 'image',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'max_file_size',
      propertyValue: '12.3',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'image',
        max_file_size: '12.3',
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the allowed extensions property of the attribute', () => {
    const state = {
      active: true,
      data: {
        type: 'image',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'allowed_extensions',
      propertyValue: ['gif', 'png'],
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'image',
        allowed_extensions: ['gif', 'png'],
      },
      isDirty: true,
      errors: [],
    });
  });

  test("I can't update the label property of the attribute", () => {
    const state = {
      active: true,
      data: {
        type: 'image',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'label',
      propertyValue: 'Nice attribute',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'image',
      },
      errors: [],
    });
  });

  test("I can't update the max length property of the attribute type custom entity", () => {
    const state = {
      active: true,
      data: {
        type: 'custom_entity',
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'max_length',
      propertyValue: 12,
    });

    expect(newState).toEqual({
      active: true,
      data: {
        type: 'custom_entity',
      },
      errors: [],
    });
  });

  test('I can cancel the attribute edition', () => {
    const state = {
      active: true,
      data: {},
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_CANCEL',
    });

    expect(newState).toEqual({
      active: false,
      data: {},
      errors: [],
      isDirty: false,
    });
  });

  test('I can submit the attribute edition', () => {
    const state = {
      active: false,
      data: {},
      errors: [
        {
          messageTemplate: 'This value should not be blank.',
          parameters: {'{{ value }}': '""'},
          message: 'This value should not be blank.',
          propertyPath: 'identifier',
          invalidValue: '',
        },
      ],
    };

    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_SUBMISSION',
    });

    expect(newState).toEqual({
      active: false,
      isSaving: true,
      data: {},
      errors: [],
    });
  });

  test('I can succeed the attribute edition', () => {
    const state = {
      active: true,
      data: {},
      errors: [],
    };

    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_SUCCEEDED',
    });

    expect(newState).toEqual({
      active: true,
      data: {},
      errors: [],
      isDirty: false,
      isSaving: false,
    });
  });

  test('I get errors on the attribute edition', () => {
    const state = {
      active: false,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };

    const errors = [
      {
        messageTemplate: 'This value should not be blank.',
        parameters: {'{{ value }}': '""'},
        message: 'This value should not be blank.',
        propertyPath: 'identifier',
        invalidValue: '',
      },
    ];

    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_ERROR_OCCURED',
      errors,
    });

    expect(newState).toEqual({
      active: false,
      isSaving: false,
      data: {
        code: '',
        labels: {},
      },
      errors,
    });
  });
});
