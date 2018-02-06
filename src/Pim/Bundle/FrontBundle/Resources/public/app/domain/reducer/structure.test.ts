import reducer from './structure';

describe('>>>REDUCER --- structure', () => {
  test('handle empty state', () => {
    expect(reducer({}, {type: 'TOTO', target: 'nothing'}))
      .toEqual({locales: [], channels: []});
  });

  test('handle receiving new locales', () => {
    expect(reducer({}, {type: 'LOCALES_UPDATED', locales: ['fr_FR']})).toEqual({locales: ['fr_FR'], channels: []});
  });

  test('handle receiving new locales', () => {
    expect(reducer({}, {type: 'CHANNELS_UPDATED', channels: ['ecommerce']})).toEqual({locales: [], channels: ['ecommerce']});
  });

  test('handle receiving new locales with existing channels', () => {
    expect(reducer(
      {channels: ['ecommerce']},
      {type: 'LOCALES_UPDATED', locales: ['en_US']}
    )).toEqual({channels: ['ecommerce'], locales: ['en_US']});
  });

  test('handle receiving new channels with existing locales', () => {
    expect(reducer(
      {locales: ['en_US']},
      {type: 'CHANNELS_UPDATED', channels: ['ecommerce']}
    )).toEqual({channels: ['ecommerce'], locales: ['en_US']});
  });

  test('handle receiving new channels with existing channels', () => {
    expect(reducer(
      {channels: ['ecommerce']},
      {type: 'CHANNELS_UPDATED', channels: ['mobile']}
    )).toEqual({channels: ['mobile'], locales: []});
  });

  test('handle receiving new locales with existing locales', () => {
    expect(reducer(
      {locales: ['en_US']},
      {type: 'LOCALES_UPDATED', locales: ['fr_FR']}
    )).toEqual({channels: [], locales: ['fr_FR']});
  });
});
