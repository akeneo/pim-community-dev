import reducer from 'akeneoreferenceentity/application/reducer/reference-entity/edit/form';

const initialState = {
  data: {
    attribute_as_label: null,
    attribute_as_image: null,
    identifier: '',
    code: '',
    labels: {},
    image: null,
  },
  errors: [],
  state: {
    isDirty: false,
    originalData: '',
  },
};

describe('akeneo > reference entity > application > reducer > reference-entity --- edit', () => {
  test('I ignore other commands', () => {
    const newState = reducer(initialState, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(initialState);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual(initialState);
  });

  test('I can receive a reference entity', () => {
    const state = {};
    const normalizedReferenceEntity = {
      identifier: 'designer',
      labels: {
        en_US: 'Designer',
      },
      image: null,
    };
    const newState = reducer(state, {
      type: 'REFERENCE_ENTITY_EDITION_RECEIVED',
      referenceEntity: normalizedReferenceEntity,
    });

    expect(newState).toEqual({
      errors: [],
      data: normalizedReferenceEntity,
      state: {isDirty: false, originalData: '{"identifier":"designer","labels":{"en_US":"Designer"},"image":null}'},
    });
  });

  test('I can update the label of the reference entity', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'REFERENCE_ENTITY_EDITION_LABEL_UPDATED',
      value: 'Famous Designer',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Famous Designer',
        },
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can add a new label of the reference entity', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'REFERENCE_ENTITY_EDITION_LABEL_UPDATED',
      value: 'Concepteur',
      locale: 'fr_FR',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
          fr_FR: 'Concepteur',
        },
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can update the image of the reference entity', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'REFERENCE_ENTITY_EDITION_IMAGE_UPDATED',
      image: {my: 'image'},
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {
          en_US: 'Designer',
        },
        image: {my: 'image'},
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I can successfully save the reference entity', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      errors: [
        {
          my: 'error',
        },
      ],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'REFERENCE_ENTITY_EDITION_SUBMISSION',
    });

    expect(newState).toEqual({
      errors: [],
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });

  test('I cannot save the reference entity', () => {
    const previousState = {
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      errors: [],
      state: {
        isDirty: false,
        originalData: '',
      },
    };
    const newState = reducer(previousState, {
      type: 'REFERENCE_ENTITY_EDITION_ERROR_OCCURED',
      errors: [
        {
          my: 'error',
        },
      ],
    });

    expect(newState).toEqual({
      errors: [
        {
          my: 'error',
        },
      ],
      data: {
        identifier: '',
        labels: {},
        image: null,
      },
      state: {isDirty: false, originalData: ''},
    });
  });
});
