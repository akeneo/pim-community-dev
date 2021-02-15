import reducer, {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {getLocalesFromChannel} from 'akeneoassetmanager/application/reducer/structure';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {getLocaleFromChannel} from '../../../../../../front/application/reducer/structure';

describe('akeneo > asset family > application > reducer --- structure[', () => {
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

  test('I can get the list of local of all channels', () => {
    const channels: Channel[] = [
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
      {
        code: 'mobile',
        labels: {},
        locales: [
          {
            code: 'de_DE',
          },
          {
            code: 'de_BE',
          },
        ],
      },
    ];

    expect(getLocalesFromChannel(channels, 'ecommerce')).toEqual([
      {
        code: 'en_US',
      },
      {
        code: 'fr_FR',
      },
    ]);

    expect(getLocalesFromChannel(channels, null)).toEqual([
      {
        code: 'en_US',
      },
      {
        code: 'fr_FR',
      },
      {
        code: 'de_DE',
      },
      {
        code: 'de_BE',
      },
    ]);
  });

  test('I can search locale by code and fallback to the first locale if locale does not exist', () => {
    const channels = [
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
      }
    ];

    expect(getLocaleFromChannel(channels, 'ecommerce', 'fr_FR')).toEqual('fr_FR');
    expect(getLocaleFromChannel(channels, 'ecommerce', 'de_DE')).toEqual('en_US');
  });

  test('I can get the list of locales for a channel', () => {
    expect(getLocales([], 'ecommerce')).toEqual([]);
    expect(
      getLocales(
        [
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
        'ecommerce'
      )
    ).toEqual([
      {
        code: 'en_US',
      },
      {
        code: 'fr_FR',
      },
    ]);
  });
});
