import reducer, {canEditLocale, canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';

const defaultState = {
  locale: [],
  assetFamily: {
    edit: false,
    assetFamilyIdentifier: '',
  },
};

describe('akeneo > asset family > application > reducer --- right', () => {
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

  test('I receive new asset family permissions', () => {
    const newState = reducer(defaultState, {
      type: 'ASSET_FAMILY_PERMISSIONS_CHANGED',
      assetFamilyPermission: [
        {
          assetFamilyIdentifier: 'designer',
          edit: true,
        },
      ],
    });

    expect(newState).toEqual({
      ...defaultState,
      assetFamily: [
        {
          assetFamilyIdentifier: 'designer',
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

  test('I can check if I can edit an asset family', () => {
    expect(
      canEditAssetFamily(
        {
          assetFamilyIdentifier: 'designer',
          edit: true,
        },
        'designer'
      )
    ).toBe(true);

    expect(
      canEditAssetFamily(
        {
          assetFamilyIdentifier: 'designer',
          edit: false,
        },
        'designer'
      )
    ).toBe(false);

    expect(
      canEditAssetFamily(
        {
          assetFamilyIdentifier: 'brand',
          edit: true,
        },
        'designer'
      )
    ).toBe(false);

    expect(
      canEditAssetFamily(
        {
          assetFamilyIdentifier: 'dEsIgNeR',
          edit: true,
        },
        'DeSiGnEr'
      )
    ).toBe(true);

    expect(canEditAssetFamily([], 'designer')).toBe(false);
  });
});
