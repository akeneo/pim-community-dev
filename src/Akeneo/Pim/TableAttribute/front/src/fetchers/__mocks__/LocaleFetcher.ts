import { Locale, LocaleCode, Router } from "@akeneo-pim-community/shared";

const fetchLocale = async (_router: Router, code: LocaleCode): Promise<Locale | undefined> => {
  if (code === 'en_US') {
    return new Promise(resolve => resolve({
      code: 'en_US',
      label: 'English (United States)',
      region: 'United States',
      language: 'English',
    }));
  }

  if (code === 'fr_FR') {
    return new Promise(resolve => resolve({
      code: 'fr_FR',
      label: 'French (France)',
      region: 'France',
      language: 'French',
    }));
  }

  return new Promise(resolve => resolve(undefined));
}

export {fetchLocale};
