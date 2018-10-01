import reducer from 'akeneoreferenceentity/application/reducer/structure';

describe('akeneo > enriched entity > application > reducer --- structure[', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({locales: [], channels: []});
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({locales: [], channels: []});
  });

  test('I can receive a locale list', () => {
    const newState = reducer(
      {locales: [], channels: []},
      {
        type: 'LOCALES_RECEIVED',
        locales: ['en_US', 'en_US'],
      }
    );

    expect(newState).toEqual({locales: ['en_US', 'en_US'], channels: []});
  });

  test('I can receive a channel list', () => {
    const newState = reducer(
      {locales: [], channels: []},
      {
        type: 'CHANNELS_RECEIVED',
        channels: ['mobile', 'eccommerce'],
      }
    );

    expect(newState).toEqual({locales: [], channels: ['mobile', 'eccommerce']});
  });
});
