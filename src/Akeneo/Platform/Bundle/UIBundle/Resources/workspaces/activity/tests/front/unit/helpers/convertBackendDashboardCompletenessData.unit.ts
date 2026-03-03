import {convertBackendDashboardCompletenessData} from '../../../../src/helpers';
import {BackendCompletenessData} from '../../../../src/domain';
import {ChannelsLocalesCompletenessRatios} from '@akeneo-pim-community/enrichment/src/models';

const data: BackendCompletenessData = {
  print: {
    labels: {
      de_DE: 'Drucken',
      en_US: 'Print',
      fr_FR: 'Impression',
    },
    total: 1239,
    complete: 102,
    locales: {
      'German (Germany)': 110,
      'English (United States)': 343,
      'French (France)': 150,
    },
  },
  mobile: {
    labels: {
      de_DE: 'Mobil',
      en_US: 'Mobile',
      fr_FR: 'Mobile FR',
    },
    total: 1239,
    complete: 65,
    locales: {
      'German (Germany)': 71,
      'English (United States)': 256,
      'French (France)': 88,
    },
  },
  ecommerce: {
    labels: {
      de_DE: 'Ecommerce',
      en_US: 'Ecommerce',
      fr_FR: 'Ecommerce FR',
    },
    total: 2000,
    complete: 1999,
    locales: {
      'French (France)': 1999,
    },
  },
};

test('It calculate the completeness by channels and by locales', () => {
  const result: ChannelsLocalesCompletenessRatios = convertBackendDashboardCompletenessData(data, 'en_US');
  expect(result).toEqual({
    Print: {
      channelRatio: 8,
      localesRatios: {
        'English (United States)': 27,
        'German (Germany)': 8,
        'French (France)': 12,
      },
    },
    Mobile: {
      channelRatio: 5,
      localesRatios: {
        'English (United States)': 20,
        'German (Germany)': 5,
        'French (France)': 7,
      },
    },
    Ecommerce: {
      channelRatio: 99,
      localesRatios: {
        'French (France)': 99,
      },
    },
  });
});
