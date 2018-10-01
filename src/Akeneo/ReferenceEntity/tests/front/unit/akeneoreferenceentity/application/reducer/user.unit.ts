import reducer from 'akeneoreferenceentity/application/reducer/user';

describe('akeneo > enriched entity > application > reducer --- user', () => {
  test('I can initialize an empty state', () => {
    const newState = reducer(undefined, {
      type: 'GO_FIRST_PAGE',
    });

    expect(newState).toEqual({});
  });

  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
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
    });

    expect(newState).toEqual({
      catalogChannel: 'ecommerce',
    });
  });

  test('I throw an error if the event is malformed', () => {
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
        type: 'LOCALE_CHANGED',
        target: 'catalog',
        channel: 'ecommerce',
      });
    }).toThrow();
  });
});
