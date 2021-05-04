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
    complete: 569,
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
    complete: 415,
    locales: {
      'German (Germany)': 71,
      'English (United States)': 256,
      'French (France)': 88,
    },
  },
};

test('', () => {
  const result: ChannelsLocalesCompletenessRatios = convertBackendDashboardCompletenessData(data, 'en_US');
  expect(result).toEqual({
    Print: {
      channelRatio: 15,
      localesRatios: {
        'English (United States)': 28,
        'German (Germany)': 9,
        'French (France)': 12,
      },
    },
    Mobile: {
      channelRatio: 11,
      localesRatios: {
        'English (United States)': 21,
        'German (Germany)': 6,
        'French (France)': 7,
      },
    },
  });
});
