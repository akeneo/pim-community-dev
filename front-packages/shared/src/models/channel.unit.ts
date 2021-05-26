import {denormalizeChannel, getChannelLabel, getAllLocales} from './channel';
import {denormalizeLocale} from './locale';

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
    expect(getAllLocales([ecommerce, mobile])).toEqual([
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
});
