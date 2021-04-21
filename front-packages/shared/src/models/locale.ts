type LocaleCode = string;
type LocaleLabel = string;
type LocaleRegion = string;
type LocaleLanguage = string;

type Locale = {
  code: LocaleCode;
  label: LocaleLabel;
  region: LocaleRegion;
  language: LocaleLanguage;
};

const denormalizeLocale = (locale: any): Locale => {
  if (!isLocale(locale)) {
    throw new Error('Invalid locale');
  }

  return {...locale};
};

const isLocales = (locales: any): locales is Locale[] => {
  if (!Array.isArray(locales)) {
    return false;
  }

  return !locales.some((locale: any) => {
    return !isLocale(locale);
  });
};

const isLocale = (locale: any): locale is Locale =>
  'string' === typeof locale.code &&
  'string' === typeof locale.label &&
  'string' === typeof locale.region &&
  'string' === typeof locale.language;

const createLocaleFromCode = (code: LocaleCode): Locale => {
  if ('string' !== typeof code) {
    throw new Error(`CreateLocaleFromCode expects a string as parameter (${typeof code} given)`);
  }

  const [language, region] = code.split('_');

  return {
    code,
    label: code,
    region: region.toLowerCase(),
    language,
  };
};

const localeExists = (locales: Locale[], currentLocale: LocaleCode) => {
  return locales.some(({code}: Locale) => code === currentLocale);
};

export {createLocaleFromCode, denormalizeLocale, localeExists, isLocales, isLocale};
export type {Locale, LocaleCode, LocaleLabel, LocaleRegion, LocaleLanguage};
