import reducer from 'akeneoreferenceentity/application/reducer/right';

const defaultState = {
  locale: [],
  referenceEntity: {
    edit: false,
    referenceEntityIdentifier: '',
  },
};

describe('akeneo > reference entity > application > reducer --- right', () => {
  test('I can initialize an empty state', () => {
    const newState = reducer(undefined, {
      type: 'GRID_GO_FIRST_PAGE',
    });

    expect(newState).toEqual(defaultState);
  });

  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I receive new locale permissions', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'LOCALE_PERMISSIONS_CHANGED',
      localePermissions: [
        {
          code: 'en_US',
          edit: true,
        },
      ],
    });

    expect(newState).toEqual({
      ...defaultState,
      locale: [
        {
          code: 'en_US',
          edit: true,
        },
      ],
    });
  });
});
