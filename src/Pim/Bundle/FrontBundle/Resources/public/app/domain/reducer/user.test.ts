import reducer from './user';

describe('>>>REDUCER --- user', () => {
  test('handle empty state', () => {
    expect(reducer(undefined, {type: 'TOTO', target: 'nothing'})).toEqual({});
  });

  test('handle switch on new locale', () => {
    expect(reducer(undefined, {type: 'LOCALE_CHANGED', target: 'copyLocale', locale: 'fr_FR'})).toEqual({copyLocale: 'fr_FR'});
  });

  test('handle switch on existing target locale', () => {
    expect(reducer(
      {catalogLocale: 'fr_FR'},
      {type: 'LOCALE_CHANGED', target: 'catalogLocale', locale: 'en_US'}
    )).toEqual({catalogLocale: 'en_US'});
  });

  test('handle switch on existing target channel', () => {
    expect(reducer(
      {catalogLocale: 'fr_FR', catalogChannel: 'mobile'},
      {type: 'CHANNEL_CHANGED', target: 'catalogChannel', channel: 'ecommerce'}
    )).toEqual({catalogLocale: 'fr_FR', catalogChannel: 'ecommerce'});
  });

  test('handle switch on existing wrong value', () => {
    expect(reducer(
      {},
      {type: 'CHANNEL_CHANGED', target: 'catalogChannel', locale: 'ecommerce'}
    )).toEqual({});
  });
});
