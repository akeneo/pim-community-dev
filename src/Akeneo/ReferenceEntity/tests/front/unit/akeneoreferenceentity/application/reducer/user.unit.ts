import reducer from 'akeneoreferenceentity/application/reducer/user';

describe('akeneo > reference entity > application > reducer --- user', () => {
  test('I can initialize an empty state', () => {
    const newState = reducer(undefined, {
      type: 'GRID_GO_FIRST_PAGE',
    });

    expect(newState).toEqual({
      catalogChannel: '',
      catalogLocale: '',
      defaultCatalogLocale: '',
      uiLocale: '',
    });
  });

  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can change a default locale for a given target', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'DEFAULT_LOCALE_CHANGED',
      target: 'catalog',
      locale: 'fr_FR',
    });

    expect(newState).toEqual({
      catalogLocale: 'fr_FR',
    });
  });

  test('I can change a locale for a given target', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'LOCALE_CHANGED',
      target: 'catalog',
      locale: 'en_US',
    });

    expect(newState).toEqual({
      catalogLocale: 'en_US',
    });
  });

  test('I can change a channel for a given target', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'CHANNEL_CHANGED',
      target: 'catalog',
      channel: 'ecommerce',
      channels: [],
    });

    expect(newState).toEqual({
      catalogChannel: 'ecommerce',
    });
  });

  test('I can change a channel for a given target implying a locale change', () => {
    const state = {
      catalogLocale: 'de_DE',
    };
    const newState = reducer(state, {
      type: 'CHANNEL_CHANGED',
      target: 'catalog',
      channel: 'ecommerce',
      channels: [
        {
          code: 'ecommerce',
          locales: [
            {
              code: 'en_US',
            },
            {
              code: 'fr_FR',
            },
          ],
        },
      ],
    });

    expect(newState).toEqual({
      catalogChannel: 'ecommerce',
      catalogLocale: 'en_US',
    });
  });

  test('I throw an error if the event is malformed', () => {
    expect(() => {
      reducer(state, {
        type: 'DEFAULT_LOCALE_CHANGED',
        target: 'catalog',
        locale: 123,
      });
    }).toThrow();

    const state = {};
    expect(() => {
      reducer(state, {
        type: 'CHANNEL_CHANGED',
        target: 'catalog',
        locale: 'ecommerce',
      });
    }).toThrow();
    expect(() => {
      reducer(state, {
        type: 'CHANNEL_CHANGED',
        target: 'catalog',
        channel: 'ecommerce',
      });
    }).toThrow();

    expect(() => {
      reducer(state, {
        type: 'LOCALE_CHANGED',
        target: 'catalog',
        channel: 'ecommerce',
      });
    }).toThrow();
  });
});
