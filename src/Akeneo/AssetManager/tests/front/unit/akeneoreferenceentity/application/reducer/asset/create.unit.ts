import reducer from 'akeneoreferenceentity/application/reducer/record/create';

describe('akeneo > reference entity > application > reducer > record --- create', () => {
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

    expect(newState).toEqual({active: false, data: {code: '', labels: {}}, errors: []});
  });

  test('I can start the creation of a new record', () => {
    const state = {
      active: false,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'RECORD_CREATION_START',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the code of the record', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'RECORD_CREATION_RECORD_CODE_UPDATED',
      value: 'code_test',
    });

    expect(newState).toEqual({
      active: true,
      data: {
        code: 'code_test',
        labels: {},
      },
      errors: [],
    });
  });

  test('I can update the label of the record', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'RECORD_CREATION_LABEL_UPDATED',
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
      type: 'RECORD_CREATION_LABEL_UPDATED',
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
      type: 'RECORD_CREATION_LABEL_UPDATED',
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

  test('I can cancel the record creation', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'RECORD_CREATION_CANCEL',
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

  test('I can dismiss the record creation', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };
    const newState = reducer(state, {
      type: 'DISMISS',
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

  test('I can submit the record creation', () => {
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
      type: 'RECORD_CREATION_SUBMISSION',
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

  test('I can succeed the record creation', () => {
    const state = {
      active: true,
      data: {
        code: '',
        labels: {},
      },
      errors: [],
    };

    const newState = reducer(state, {
      type: 'RECORD_CREATION_SUCCEEDED',
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

  test('I get errors on the record creation', () => {
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
      type: 'RECORD_CREATION_ERROR_OCCURED',
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
