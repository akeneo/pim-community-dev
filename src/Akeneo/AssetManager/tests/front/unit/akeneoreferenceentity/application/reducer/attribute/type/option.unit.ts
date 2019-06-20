import {reducer} from 'akeneoreferenceentity/application/reducer/attribute/type/option';
import {editOptionsReducer} from 'akeneoreferenceentity/application/reducer/attribute/type/option';
import {NormalizedOption} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';

const normalizedOptions = [
  {
    code: 'red',
    labels: {
      fr_FR: 'Rouge',
      en_US: 'Red',
    },
  },
  {
    code: 'green',
    labels: {
      fr_FR: 'Vert',
      en_US: 'Green',
    },
  },
];
const stringifiedOptions =
  '[{"code":"red","labels":{"fr_FR":"Rouge","en_US":"Red"}},{"code":"green","labels":{"fr_FR":"Vert","en_US":"Green"}}]';

describe('akeneo > reference entity > application > reducer > attribute > type > option --- edit', () => {
  test('I call the option reducer', () => {
    const state = {type: 'option'};
    const newState = reducer(state, 'label', 'designer');
    expect(newState).toBe(state);
  });

  test('I ignore other commands', () => {
    const state = {};
    const newState = editOptionsReducer(state, {type: 'ANOTHER_ACTION'});
    expect(newState).toBe(state);
  });

  test('It generates default state', () => {
    const newState = editOptionsReducer(undefined, {type: 'ANOTHER_ACTION'});
    expect(newState).toEqual({
      isActive: false,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [],
      currentOptionId: 0,
      numberOfLockedOptions: 0,
    });
  });

  test('I start to manage the options of the attribute', () => {
    const state = editOptionsReducer(undefined, {});
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_START',
      options: normalizedOptions,
    });
    expect(newState).toEqual({
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
      numberOfLockedOptions: 2,
    });
  });

  test('I leave the modal or cancel the edition', () => {
    const state = {
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
    };

    const cancelState = editOptionsReducer(state, {type: 'OPTIONS_EDITION_CANCEL'});
    const dismissState = editOptionsReducer(state, {type: 'DISMISS'});

    const expectedState = {
      isActive: false,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
    };
    expect(cancelState).toEqual(expectedState);
    expect(dismissState).toEqual(expectedState);
  });

  test('I see the label translations panel when I select one option', () => {
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [
        {
          code: 'redsocks_',
          labels: {
            fr_FR: 'Red socks!',
          },
        },
        {
          code: 'bluesocks_',
          labels: {
            fr_FR: 'Blue socks!',
          },
        },
      ],
      currentOptionId: 0,
    };
    const dismissState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_SELECTED',
      id: 1,
    });
    expect(dismissState).toEqual({
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [
        {
          code: 'redsocks_',
          labels: {
            fr_FR: 'Red socks!',
          },
        },
        {
          code: 'bluesocks_',
          labels: {
            fr_FR: 'Blue socks!',
          },
        },
      ],
      currentOptionId: 1,
    });
  });

  test('I edit the label of a new option and it auto-fills the code with a sanitized value', () => {
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [],
      currentOptionId: 0,
    };
    const dismissState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_LABEL_UPDATED',
      id: 0,
      label: 'Red socks!',
      locale: 'fr_FR',
    });
    expect(dismissState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [
        {
          code: 'redsocks_',
          labels: {
            fr_FR: 'Red socks!',
          },
        },
      ],
      currentOptionId: 0,
    });
  });

  test('I edit the label of an existing option and it auto completes the code with a sanitized value', () => {
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
    };
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_LABEL_UPDATED',
      id: 0,
      label: 'Reds',
      locale: 'en_US',
    });
    expect(newState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData:
        '[{"code":"red","labels":{"fr_FR":"Rouge","en_US":"Red"}},{"code":"green","labels":{"fr_FR":"Vert","en_US":"Green"}}]',
      options: [
        {
          code: 'red',
          labels: {
            fr_FR: 'Rouge',
            en_US: 'Reds',
          },
        },
        normalizedOptions[1],
      ],
      currentOptionId: 0,
    });
  });

  test('When I empty the code and label, the option is automatically removed', () => {
    const options = [
      {
        code: 'Red',
        labels: {},
      },
      {
        code: 'r',
        labels: {},
      },
      {
        code: 'Blue',
        labels: {},
      },
    ];
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: JSON.stringify(options),
      options: options,
      currentOptionId: 0,
    };
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_CODE_UPDATED',
      id: 1,
      code: '',
    });
    expect(newState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: JSON.stringify(options),
      options: [options[0], options[2]],
      currentOptionId: 0,
    });
  });

  test('When I only empty the code and leave the some labels, the option is not automatically removed', () => {
    const options = [
      {
        code: 'Red',
        labels: {},
      },
      {
        code: 'r',
        labels: {
          fr_FR: 'Red',
        },
      },
      {
        code: 'Blue',
        labels: {},
      },
    ];
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: JSON.stringify(options),
      options: options,
      currentOptionId: 0,
    };
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_CODE_UPDATED',
      id: 1,
      code: '',
    });
    expect(newState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: JSON.stringify(options),
      options: [
        options[0],
        {
          code: '',
          labels: {
            fr_FR: 'Red',
          },
        },
        options[2],
      ],
      currentOptionId: 0,
    });
  });

  test('I completely remove the label for the locale', () => {
    const originalData = '[{"code":"r","labels":{"fr_FR":"R","en_US":"Red"}}]';
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: originalData,
      options: [{code: 'r', labels: {fr_FR: 'R', en_US: 'Red'}}],
      currentOptionId: 0,
    };
    const dismissState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_LABEL_UPDATED',
      id: 0,
      label: '',
      locale: 'fr_FR',
    });
    expect(dismissState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: originalData,
      options: [
        {
          code: 'r',
          labels: {
            en_US: 'Red',
          },
        },
      ],
      currentOptionId: 0,
    });
  });

  test('If I updated manually the code, the label is not updated', () => {
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
    };
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_CODE_UPDATED',
      id: 0,
      code: 'red_burn',
    });
    expect(newState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: stringifiedOptions,
      options: [
        {
          code: 'red_burn',
          labels: {
            fr_FR: 'Rouge',
            en_US: 'Red',
          },
        },
        normalizedOptions[1],
      ],
      currentOptionId: 0,
    });
  });

  test('I updated the code of a new option', () => {
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [],
      currentOptionId: 0,
    };
    const dismissState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_CODE_UPDATED',
      id: 0,
      code: 'red',
    });
    expect(dismissState).toEqual({
      isActive: true,
      isDirty: true,
      isSaving: false,
      errors: [],
      originalData: '',
      options: [
        {
          code: 'red',
          labels: {},
        },
      ],
      currentOptionId: 0,
    });
  });

  test('I delete the first option which is was locked', () => {
    const state = {
      isActive: true,
      isDirty: true,
      isSaving: true,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
      numberOfLockedOptions: 1,
    };
    const dismissState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_DELETE',
      id: 0,
    });
    expect(dismissState).toEqual({
      ...state,
      options: [normalizedOptions[1]],
      numberOfLockedOptions: 0,
    });
  });

  test("I delete an option which wasn't locked", () => {
    const state = {
      isActive: true,
      isDirty: true,
      isSaving: true,
      errors: [],
      originalData: stringifiedOptions,
      options: normalizedOptions,
      currentOptionId: 0,
      numberOfLockedOptions: 1,
    };
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_DELETE',
      id: 1,
    });
    expect(newState).toEqual({
      ...state,
      options: [normalizedOptions[0]],
      numberOfLockedOptions: 1,
    });
  });

  test('I submit the options and it cleans the errors', () => {
    const state = {
      isActive: true,
      isDirty: false,
      isSaving: false,
      errors: [
        {
          messageTemplate: 'message_template',
          parameters: {},
          message: 'An error occured for the option "red"',
          propertyPath: 'option.red.code',
          invalidValue: 're-d',
        },
      ],
      originalData: '',
      options: [],
      currentOptionId: 0,
    };
    const dismissState = editOptionsReducer(state, {type: 'OPTIONS_EDITION_SUBMISSION'});
    expect(dismissState).toEqual({
      ...state,
      isSaving: true,
      errors: [],
    });
  });

  test('The options have been successfully updated', () => {
    const state = {
      isActive: true,
      isDirty: true,
      isSaving: true,
      errors: [],
      originalData: '',
      options: [],
      currentOptionId: 0,
    };
    const dismissState = editOptionsReducer(state, {type: 'OPTIONS_EDITION_SUCCEEDED'});
    expect(dismissState).toEqual({
      ...state,
      isActive: false,
      isDirty: false,
      numberOfLockedOptions: 0,
    });
  });

  test('An error occured', () => {
    const errors = [
      {
        messageTemplate: 'an error',
        parameters: {},
        message: 'An error',
        propertyPath: 'option.re-d.0',
        invalidValue: 're-d',
      },
    ];
    const state = {
      isActive: true,
      isDirty: true,
      isSaving: true,
      errors: [],
      originalData: '',
      options: [],
      currentOptionId: 0,
    };
    const newState = editOptionsReducer(state, {
      type: 'OPTIONS_EDITION_ERROR_OCCURED',
      errors: errors,
    });
    expect(newState).toEqual({
      ...state,
      isSaving: false,
      errors: errors,
    });
  });
});
