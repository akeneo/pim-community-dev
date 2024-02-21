import {formatProductCompleteness} from '@akeneo-pim-community/enrichment';

test('it formats a list of product completeness', () => {
  const rawCompleteness = [
    {
      channel: 'ecommerce',
      labels: {
        en_US: 'Ecommerce',
        fr_FR: 'i-commerce',
      },
      stats: {
        total: 3,
        complete: 0,
        average: 50,
      },
      locales: {
        en_US: {
          completeness: {
            required: 4,
            missing: 2,
            ratio: 70,
            locale: 'en_US',
            channel: 'ecommerce',
          },
          label: 'English (United States)',
        },
        fr_FR: {
          completeness: {
            required: 4,
            missing: 2,
            ratio: 40,
            locale: 'fr_FR',
            channel: 'ecommerce',
          },
          label: 'French (France)',
        },
      },
    },
    {
      channel: 'print',
      labels: {
        en_US: 'Print',
        fr_FR: 'Impression',
      },
      stats: {
        total: 3,
        complete: 0,
        average: 58,
      },
      locales: {
        en_US: {
          completeness: {
            required: 4,
            missing: 1,
            ratio: 75,
            locale: 'en_US',
            channel: 'print',
          },
          label: 'English (United States)',
        },
        fr_FR: {
          completeness: {
            required: 4,
            missing: 2,
            ratio: 50,
            locale: 'fr_FR',
            channel: 'print',
          },
          label: 'French (France)',
        },
      },
    },
  ];
  const formattedCompletenessList = formatProductCompleteness(rawCompleteness, 'en_US');

  expect(formattedCompletenessList).toEqual({
    Ecommerce: {
      channelRatio: 50,
      localesRatios: {
        'English (United States)': 70,
        'French (France)': 40,
      },
    },
    Print: {
      channelRatio: 58,
      localesRatios: {
        'English (United States)': 75,
        'French (France)': 50,
      },
    },
  });
});
