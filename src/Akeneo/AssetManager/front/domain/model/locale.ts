import {isString} from 'akeneoassetmanager/domain/model/utils';

export type LocaleReference = LocaleCode | null;

export type LocaleCode = string;
export type LocaleLabel = string;
export type LocaleRegion = string;
export type LocaleLanguage = string;

type Locale = {
  code: LocaleCode;
  label: LocaleLabel;
  region: LocaleRegion;
  language: LocaleLanguage;
};

export default Locale;

export const denormalizeLocale = (locale: any): Locale => {
  if (!isString(locale.code)) {
    throw new Error('Locale expects a string as code to be created');
  }
  if (!isString(locale.label)) {
    throw new Error('Locale expects a string as label to be created');
  }
  if (!isString(locale.region)) {
    throw new Error('Locale expects a string as region to be created');
  }
  if (!isString(locale.language)) {
    throw new Error('Locale expects a string as language to be created');
  }

  return {...locale};
};

export const createLocaleFromCode = (code: LocaleCode): Locale => {
  if (!isString(code)) {
    throw new Error(`CreateLocaleFromCode expects a string as parameter (${typeof code} given`);
  }

  const [language, region] = code.split('_');

  return {
    code,
    label: code,
    region: region.toLowerCase(),
    language,
  };
};
