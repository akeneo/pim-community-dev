import {
  Channel,
  denormalizeChannel,
  getChannelLabel,
  getLocalesFromChannel,
  getAllLocalesFromChannels,
  getLocaleFromChannel,
  getCurrencyCodesFromChannelReference,
} from './channel';
import {denormalizeLocale} from './locale';

const baseChannel = {
  category_tree: '',
  conversion_units: [],
  currencies: [],
  labels: {},
  meta: {
    created: '',
    form: '',
    id: 1,
    updated: '',
  },
};

const baseLocale = {
  code: '',
  label: '',
  region: '',
  language: '',
};

describe('akeneo > shared > model --- channel', () => {
  test('I can create a new channel from a normalized one', () => {
    const channel = denormalizeChannel({
      code: 'ecommerce',
      labels: {en_US: 'E-commerce'},
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English',
        },
      ],
    });
    expect(channel.code).toBe('ecommerce');
    expect(getChannelLabel(channel, 'en_US')).toBe('E-commerce');
    expect(getChannelLabel(channel, 'fr_FR')).toBe('[ecommerce]');
  });

  test('I cannot create a new channel with invalid parameters', () => {
    expect(() => {
      denormalizeChannel({labels: {}, locales: []});
    }).toThrow('Channel expects a string as code to be created');

    expect(() => {
      denormalizeChannel({code: 'toto', labels: 'labels', locales: []});
    }).toThrow('Channel expects a label collection as labels to be created');

    expect(() => {
      denormalizeChannel({
        code: 'toto',
        labels: {},
        locales: [
          denormalizeLocale({
            code: 'en_US',
            label: 'English (United States)',
            region: 'United States',
            language: 'English',
          }),
          {},
        ],
      });
    }).toThrow('Channel expects an array as locales to be created');

    expect(() => {
      denormalizeChannel({code: 'toto', labels: {}, locales: [{}]});
    }).toThrow('Channel expects an array as locales to be created');
    expect(() => {
      denormalizeChannel({
        code: 'toto',
        labels: {},
        locales: [
          {
            code: 'en_US',
          },
        ],
      });
    }).toThrow('Channel expects an array as locales to be created');
    expect(() => {
      denormalizeChannel({code: 'toto', labels: {}, locales: 'locales'});
    }).toThrow('Channel expects an array as locales to be created');
  });
  test('I can get all locales from multiple channels', () => {
    const ecommerce = denormalizeChannel({
      code: 'ecommerce',
      labels: {en_US: 'E-commerce'},
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English',
        },
      ],
    });
    const mobile = denormalizeChannel({
      code: 'mobile',
      labels: {en_US: 'Mobile'},
      locales: [
        {
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English',
        },
        {
          code: 'fr_FR',
          label: 'French (France)',
          region: 'France',
          language: 'French',
        },
      ],
    });
    expect(getAllLocalesFromChannels([ecommerce, mobile])).toEqual([
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
      {
        code: 'fr_FR',
        label: 'French (France)',
        region: 'France',
        language: 'French',
      },
    ]);
  });

  test('I can get the list of local of all channels', () => {
    const channels: Channel[] = [
      {
        ...baseChannel,
        code: 'ecommerce',
        locales: [
          {
            ...baseLocale,
            code: 'en_US',
          },
          {
            ...baseLocale,
            code: 'fr_FR',
          },
        ],
      },
      {
        ...baseChannel,
        code: 'mobile',
        labels: {},
        locales: [
          {
            ...baseLocale,
            code: 'de_DE',
          },
          {
            ...baseLocale,
            code: 'de_BE',
          },
        ],
      },
    ];

    expect(getLocalesFromChannel(channels, 'ecommerce')).toEqual([
      {
        ...baseLocale,
        code: 'en_US',
      },
      {
        ...baseLocale,
        code: 'fr_FR',
      },
    ]);

    expect(getLocalesFromChannel(channels, null)).toEqual([
      {
        ...baseLocale,
        code: 'en_US',
      },
      {
        ...baseLocale,
        code: 'fr_FR',
      },
      {
        ...baseLocale,
        code: 'de_DE',
      },
      {
        ...baseLocale,
        code: 'de_BE',
      },
    ]);
  });

  test('I can search locale by code and fallback to the first locale if locale does not exist', () => {
    const channels = [
      {
        ...baseChannel,
        code: 'ecommerce',
        locales: [
          {
            ...baseLocale,
            code: 'en_US',
          },
          {
            ...baseLocale,
            code: 'fr_FR',
          },
        ],
      },
    ];

    expect(getLocaleFromChannel(channels, 'ecommerce', 'fr_FR')).toEqual('fr_FR');
    expect(getLocaleFromChannel(channels, 'ecommerce', 'de_DE')).toEqual('en_US');
    expect(getLocaleFromChannel(channels, 'ecommerce', null)).toEqual(null);
  });

  test('I can get the list of locales for a channel', () => {
    expect(getLocalesFromChannel([], 'ecommerce')).toEqual([]);
    expect(
      getLocalesFromChannel(
        [
          {
            ...baseChannel,
            code: 'ecommerce',
            locales: [
              {
                ...baseLocale,
                code: 'en_US',
              },
              {
                ...baseLocale,
                code: 'fr_FR',
              },
            ],
          },
        ],
        'ecommerce'
      )
    ).toEqual([
      {
        ...baseLocale,
        code: 'en_US',
      },
      {
        ...baseLocale,
        code: 'fr_FR',
      },
    ]);
  });

  test('I can extract currency codes from all channels', () => {
    expect(getCurrencyCodesFromChannelReference([], null)).toEqual([]);
    expect(
      getCurrencyCodesFromChannelReference(
        [
          {
            ...baseChannel,
            code: 'ecommerce',
            locales: [],
            currencies: ['USD', 'EUR'],
          },
          {
            ...baseChannel,
            code: 'mobile',
            locales: [],
            currencies: ['DKK', 'EUR'],
          },
        ],
        null
      )
    ).toEqual(['USD', 'EUR', 'DKK']);
  });

  test('I can extract currency codes from a channel', () => {
    expect(getCurrencyCodesFromChannelReference([], 'ecommerce')).toEqual([]);
    expect(
      getCurrencyCodesFromChannelReference(
        [
          {
            ...baseChannel,
            code: 'ecommerce',
            locales: [],
            currencies: ['USD', 'EUR'],
          },
          {
            ...baseChannel,
            code: 'mobile',
            locales: [],
            currencies: ['DKK', 'EUR'],
          },
        ],
        'ecommerce'
      )
    ).toEqual(['USD', 'EUR']);
  });
});
