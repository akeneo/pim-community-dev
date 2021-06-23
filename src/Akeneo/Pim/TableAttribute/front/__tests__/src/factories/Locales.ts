import {Locale} from '@akeneo-pim-community/shared';

export const getEnUsLocale: () => Locale = () => {
  return {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  };
};
