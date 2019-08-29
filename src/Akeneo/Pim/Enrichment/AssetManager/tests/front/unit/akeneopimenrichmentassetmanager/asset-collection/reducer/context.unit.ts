import {
  contextReducer,
  localeUpdated,
  channelUpdated,
  selectContext,
  selectCurrentLocale,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';

describe('akeneo > enrichment > asset collection > reducer > context', () => {
  test('It ignore other commands', () => {
    const state = {};
    const newState = contextReducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toMatchObject(state);
  });

  test('It should generate a default state', () => {
    const newState = contextReducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toMatchObject({locale: '', channel: ''});
  });

  test('It should update the locale in the state', () => {
    const state = {locale: '', channel: ''};
    const newState = contextReducer(state, {
      type: 'LOCALE_UPDATED',
      locale: 'en_US',
    });

    expect(newState).toMatchObject({locale: 'en_US', channel: ''});
  });

  test('It should update the channel in the state', () => {
    const state = {locale: '', channel: ''};
    const newState = contextReducer(state, {
      type: 'CHANNEL_UPDATED',
      channel: 'ecommerce',
    });

    expect(newState).toMatchObject({locale: '', channel: 'ecommerce'});
  });

  test('It should have an action to update the locale', () => {
    const locale = 'en_US';
    const expectedAction = {
      type: 'LOCALE_UPDATED',
      locale,
    };

    expect(localeUpdated(locale)).toMatchObject(expectedAction);
  });

  test('It should have an action to update the channel', () => {
    const channel = 'ecommerce';
    const expectedAction = {
      type: 'CHANNEL_UPDATED',
      channel,
    };

    expect(channelUpdated(channel)).toMatchObject(expectedAction);
  });

  test('It should be able to select the context from the state', () => {
    const state = {
      context: {channel: 'ecommerce', locale: 'en_US'},
      structure: {attributes: [], channels: [], family: null},
      values: [],
    };
    const expectedContext = {channel: 'ecommerce', locale: 'en_US'};

    expect(selectContext(state)).toMatchObject(expectedContext);
  });

  test('It should be able to select the current locale from the state', () => {
    const state = {
      context: {channel: 'ecommerce', locale: 'en_US'},
      structure: {attributes: [], channels: [], family: null},
      values: [],
    };
    const expectedCurrentLocale = 'en_US';

    expect(selectCurrentLocale(state)).toEqual(expectedCurrentLocale);
  });
});
