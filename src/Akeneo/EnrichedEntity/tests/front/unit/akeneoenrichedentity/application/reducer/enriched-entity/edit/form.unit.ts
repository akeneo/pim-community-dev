import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import reducer from 'akeneoenrichedentity/application/reducer/enriched-entity/edit/form';

const initialState = {
  data: {
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

describe('akeneo > enriched entity > application > reducer > enriched-entity --- edit', () => {
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

  test('I can receive an enriched entity', () => {
    const state = {};
    const normalizedEnrichedEntity = {
      identifier: 'designer',
      labels: {
        en_US: 'Designer',
      },
      image: null,
    };
    const newState = reducer(state, {
      type: 'ENRICHED_ENTITY_EDITION_RECEIVED',
      enrichedEntity: normalizedEnrichedEntity,
    });

    expect(newState).toEqual({
      errors: [],
      data: normalizedEnrichedEntity,
      state: {isDirty: false, originalData: '{"identifier":"designer","labels":{"en_US":"Designer"},"image":null}'},
    });
  });

  test('I can update the label of the enriched entity', () => {
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
      type: 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED',
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

  test('I can add a new label of the enriched entity', () => {
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
      type: 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED',
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

  test('I can update the image of the enriched entity', () => {
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
      type: 'ENRICHED_ENTITY_EDITION_IMAGE_UPDATED',
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

  test('I can successfully save the enriched entity', () => {
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
      type: 'ENRICHED_ENTITY_EDITION_SUBMISSION',
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

  test('I cannot save the enriched entity', () => {
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
      type: 'ENRICHED_ENTITY_EDITION_ERROR_OCCURED',
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
