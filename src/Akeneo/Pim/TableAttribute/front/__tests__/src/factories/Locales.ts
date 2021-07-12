import {Locale} from '@akeneo-pim-community/shared';

const getEnUsLocale: () => Locale = () => {
  return {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  };
};

const getFrFrLocale: () => Locale = () => {
  return {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  };
};

const getDeDeLocale: () => Locale = () => {
  return {
    code: 'de_DE',
    label: 'German (Germany)',
    region: 'Germany',
    language: 'German',
  };
};

const getLocales: () => Locale[] = () => {
  return [getEnUsLocale(), getFrFrLocale(), getDeDeLocale()];
};

export {getEnUsLocale, getFrFrLocale, getDeDeLocale, getLocales};
