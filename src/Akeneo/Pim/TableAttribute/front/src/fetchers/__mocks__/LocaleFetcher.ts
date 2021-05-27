import {Locale, LocaleCode, Router} from '@akeneo-pim-community/shared';

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

const fetchLocale = async (_router: Router, code: LocaleCode): Promise<Locale | undefined> => {
  if (code === 'en_US') {
    return new Promise(resolve => resolve(en_US));
  }

  if (code === 'fr_FR') {
    return new Promise(resolve => resolve(fr_FR));
  }

  return new Promise(resolve => resolve(undefined));
};

const fetchActivatedLocales = async (_router: Router): Promise<Locale[]> => {
  return new Promise(resolve => resolve([en_US, fr_FR, de_DE]));
};

export {fetchLocale, fetchActivatedLocales};
