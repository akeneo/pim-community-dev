import reducer from 'akeneoreferenceentity/application/reducer/attribute/create';
import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';

describe('akeneo > reference entity > application > reducer > attribute --- create', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
        record_type: null,
      },
      errors: [],
    });
  });

  test('I can start the creation of a new attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_START',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
        record_type: null,
      },
      errors: [],
    });
  });

  test('I can update the code of the attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_CODE_UPDATED',
      value: 'code_test',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: 'code_test',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the value per locale of the attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_VALUE_PER_LOCALE_UPDATED',
      value_per_locale: true,
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: true,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the value per channel of the attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_VALUE_PER_CHANNEL_UPDATED',
      value_per_channel: true,
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        type: 'text',
        value_per_locale: false,
        value_per_channel: true,
        labels: {},
      },
      errors: [],
    });
  });

  test('I can reset the record type after updating the type of the attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'text',
        record_type: null,
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_TYPE_UPDATED',
      attribute_type: 'image',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        type: 'image',
        record_type: null,
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the type of the attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'text',
        record_type: null,
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_TYPE_UPDATED',
      attribute_type: 'record',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        type: 'record',
        record_type: null,
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the record type of the attribute', () => {
    const state = {
      active: false,
      data: {
        code: '',
        type: 'record',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_RECORD_TYPE_UPDATED',
      record_type: 'brand',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        type: 'record',
        record_type: 'brand',
        value_per_locale: false,
        value_per_channel: false,
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the label of the attribute', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_LABEL_UPDATED',
      value: 'label testé-/$',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        code: 'labeltest____',
        labels: {
          en_US: 'label testé-/$',
        },
      },
      errors: [],
    });
  });

  test('If I updated manually the code, the label is not updated', () => {
    const state = {
      active: true,
      data: {
        code: 'my_code',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_LABEL_UPDATED',
      value: 'label testé-/$',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        code: 'my_code',
        labels: {
          en_US: 'label testé-/$',
        },
      },
      errors: [],
    });
  });

  test('I can add a new label and it will update the code', () => {
    const state = {
      active: true,
      data: {
        code: 'previouslabel',
        labels: {
          en_US: 'previous label',
        },
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_LABEL_UPDATED',
      value: 'new label',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        code: 'newlabel',
        labels: {
          en_US: 'new label',
        },
      },
      errors: [],
    });
  });

  test('I can cancel the attribute creation', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_CANCEL',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    });
  });

  test('I can submit the attribute creation', () => {
    const state = {
      active: false,
      data: {
        code: '',
        labels: {},
      },
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
      type: 'ATTRIBUTE_CREATION_SUBMISSION',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    });
  });

  test('I can succeed the attribute creation', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };

    const newState = reducer(state, {
      type: 'ATTRIBUTE_CREATION_SUCCEEDED',
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    });
  });

  test('I get errors on the attribute creation', () => {
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
      type: 'ATTRIBUTE_CREATION_ERROR_OCCURED',
      errors,
    });

    expect(newState).toEqual({
      active: false,
      data: {
        code: '',
        labels: {},
      },
      errors,
    });
  });
});
