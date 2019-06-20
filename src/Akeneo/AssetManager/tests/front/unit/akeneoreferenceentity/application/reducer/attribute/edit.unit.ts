import {editReducer} from 'akeneoassetmanager/application/reducer/attribute/edit';
import {ValidationRuleOption} from 'akeneoassetmanager/domain/model/attribute/type/text/validation-rule';

const normalizedDescription = {
  identifier: 'description_1234',
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

const reducer = editReducer(() => state => state);
const modififerReducer = editReducer(() => state => {
  return {...state, michel: 'didier'};
});

describe('akeneo > asset family > application > reducer > attribute --- edit', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('It generate default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({
      isActive: false,
      data: {
        identifier: '',
        asset_family_identifier: '',
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
        validation_rule: ValidationRuleOption.None,
        regular_expression: null,
      },
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: '',
    });
  });

  test('I can start the edition of an attribute', () => {
    const state = {
      isActive: false,
      data: {},
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_START',
      attribute: normalizedDescription,
    });

    expect(newState).toEqual({
      isActive: true,
      data: normalizedDescription,
      isDirty: false,
      originalData: JSON.stringify(normalizedDescription),
      errors: [],
    });
  });

  test('the list of attributes can be updated without effect', () => {
    const state = {
      isActive: false,
      data: {},
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_UPDATED',
      attribute: normalizedDescription,
    });

    expect(newState).toEqual({
      isActive: false,
      data: {},
      errors: [],
    });
  });

  test('the list of attributes can be updated', () => {
    const newAttribute = {...normalizedDescription, labels: {en_US: 'new description'}};
    const state = {
      isActive: true,
      data: normalizedDescription,
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_UPDATED',
      attributes: [newAttribute],
    });

    expect(newState).toEqual({
      isActive: true,
      data: newAttribute,
      isDirty: false,
      originalData: JSON.stringify(newAttribute),
      errors: [],
    });
  });

  test('the list of attributes does not have any effect', () => {
    const newAttribute = {...normalizedDescription, identifier: 'new_description_1234'};
    const state = {
      isActive: true,
      data: normalizedDescription,
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_UPDATED',
      attributes: [newAttribute],
    });

    expect(newState).toEqual({
      isActive: true,
      data: normalizedDescription,
      originalData: '',
      isDirty: false,
      errors: [],
    });
  });

  test('I can update the label of the attribute', () => {
    const state = {
      isActive: true,
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
      isActive: true,
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
      isActive: true,
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
      isActive: true,
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
      isActive: true,
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
      isActive: true,
      data: {
        is_required: false,
      },
      isDirty: false,
      errors: [],
    });
  });

  test('I can update the is_required property of the attribute', () => {
    const state = {
      isActive: true,
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
      isActive: true,
      data: {
        is_required: false,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can update the is_required property of the attribute', () => {
    const state = {
      isActive: true,
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
      isActive: true,
      data: {
        is_required: false,
      },
      isDirty: true,
      errors: [],
    });
  });

  test('I can cancel the attribute edition', () => {
    const state = {
      isActive: true,
      data: {},
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_CANCEL',
    });

    expect(newState).toEqual({
      isActive: false,
      data: {},
      errors: [],
      isDirty: false,
    });
  });

  test('I can submit the attribute edition', () => {
    const state = {
      isActive: false,
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
      isActive: false,
      isSaving: true,
      data: {},
      errors: [],
    });
  });

  test('I can succeed the attribute edition', () => {
    const state = {
      isActive: true,
      data: {},
      errors: [],
    };

    const newState = reducer(state, {
      type: 'ATTRIBUTE_EDITION_SUCCEEDED',
    });

    expect(newState).toEqual({
      isActive: true,
      data: {},
      errors: [],
      isDirty: false,
      isSaving: false,
    });
  });

  test('I get errors on the attribute edition', () => {
    const state = {
      isActive: false,
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
      isActive: false,
      isSaving: false,
      data: {
        code: '',
        labels: {},
      },
      errors,
    });
  });

  test('I cannot update an additional property if the reducer did not changed anything', () => {
    const state = {
      isActive: true,
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

    expect(newState).toBe(state);
  });

  test('I cannot update an additional property of the attribute if it is not active', () => {
    const state = {
      isActive: false,
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

    expect(newState).toBe(state);
  });

  test('I can update an additional property of the attribute', () => {
    const state = {
      isActive: true,
      data: {
        type: 'text',
        is_rich_text_editor: false,
      },
      errors: [],
    };
    const newState = modififerReducer(state, {
      type: 'ATTRIBUTE_EDITION_ADDITIONAL_PROPERTY_UPDATED',
      propertyCode: 'is_textarea',
      propertyValue: true,
    });

    expect(newState).toEqual({
      isActive: true,
      data: {
        type: 'text',
        is_rich_text_editor: false,
        michel: 'didier',
      },
      errors: [],
      isDirty: true,
    });
  });
});
