import {Channel, ChannelCode, LabelCollection, Locale} from '@akeneo-pim-community/shared';

const aChannelList = (): Channel[] => {
  return [aChannel('ecommerce'), aChannel('mobile'), aChannel('print')];
};

const aChannel = (code: ChannelCode): Channel => {
  let labels: LabelCollection;
  switch (code) {
    case 'print':
      labels = {de_DE: 'Drucken', en_US: 'Print', fr_FR: 'Impression'};
      break;
    case 'mobile':
      labels = {de_DE: 'Mobil', en_US: 'Mobile', fr_FR: 'Mobile'};
      break;
    default:
      const label = 'Ecommerce';
      labels = {de_DE: label, en_US: label, fr_FR: label};
      break;
  }

  return {
    code: code,
    labels: labels,
    locales: [
      aLocale('de_DE', 'German (Germany)', 'Germany', 'Germany'),
      aLocale('en_US', 'English (United States)', 'English', 'United States'),
      aLocale('fr_FR', 'French (France)', 'French', 'France'),
    ],
    category_tree: 'master',
    conversion_units: [],
    currencies: [],
    meta: {created: {}, form: 'pim-channel-edit-form', id: 1, updated: {}},
  };
};

const aLocale = (code: string, label: string, language: string, region: string): Locale => {
  return {
    code: code,
    label: label,
    language: language,
    region: region,
  };
};

export {aChannelList, aChannel, aLocale};
