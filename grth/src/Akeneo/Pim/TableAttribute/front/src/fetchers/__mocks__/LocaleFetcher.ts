import {Locale, Router} from '@akeneo-pim-community/shared';

const en_US = {
  code: 'en_US',
  label: 'English (United States)',
  region: 'United States',
  language: 'English',
};

const fr_FR = {
  code: 'fr_FR',
  label: 'French (France)',
  region: 'France',
  language: 'French',
};

const de_DE = {
  code: 'de_DE',
  label: 'German (Germany)',
  region: 'Germany',
  language: 'German',
};

/* eslint-disable @typescript-eslint/no-unused-vars */
const fetchLocales = async (_router: Router): Promise<Locale[]> => {
  return new Promise(resolve => resolve([en_US, fr_FR, de_DE]));
};

const fetchActivatedLocales = async (_router: Router): Promise<Locale[]> => {
  return new Promise(resolve => resolve([en_US, fr_FR, de_DE]));
};

const LocaleFetcher = {
  fetchAll: fetchLocales,
  fetchActivated: fetchActivatedLocales,
  fetchUi: fetchActivatedLocales,
};

export {LocaleFetcher};
