import reducer, {canEditLocale, canEditReferenceEntity} from 'akeneoreferenceentity/application/reducer/right';

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
    const newState = reducer(defaultState, {
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

  test('I receive new reference entity permissions', () => {
    const newState = reducer(defaultState, {
      type: 'REFERENCE_ENTITY_PERMISSIONS_CHANGED',
      referenceEntityPermission: [
        {
          referenceEntityIdentifier: 'designer',
          edit: true,
        },
      ],
    });

    expect(newState).toEqual({
      ...defaultState,
      referenceEntity: [
        {
          referenceEntityIdentifier: 'designer',
          edit: true,
        },
      ],
    });
  });

  test('I can check if I can edit a locale', () => {
    expect(
      canEditLocale(
        [
          {
            code: 'en_US',
            edit: true,
          },
        ],
        'en_US'
      )
    ).toBe(true);

    expect(
      canEditLocale(
        [
          {
            code: 'en_US',
            edit: true,
          },
        ],
        'fr_FR'
      )
    ).toBe(false);

    expect(
      canEditLocale(
        [
          {
            code: 'en_US',
            edit: false,
          },
        ],
        'en_US'
      )
    ).toBe(false);

    expect(canEditLocale([], 'en_US')).toBe(false);
  });

  test('I can check if I can edit a reference entity', () => {
    expect(
      canEditReferenceEntity(
        {
          referenceEntityIdentifier: 'designer',
          edit: true,
        },
        'designer'
      )
    ).toBe(true);

    expect(
      canEditReferenceEntity(
        {
          referenceEntityIdentifier: 'designer',
          edit: false,
        },
        'designer'
      )
    ).toBe(false);

    expect(
      canEditReferenceEntity(
        {
          referenceEntityIdentifier: 'brand',
          edit: true,
        },
        'designer'
      )
    ).toBe(false);

    expect(canEditReferenceEntity([], 'designer')).toBe(false);
  });
});
