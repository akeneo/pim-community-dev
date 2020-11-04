type LocaleCode = string;

type Locale = {
  code: LocaleCode;
  label: string;
  region: string;
  language: string;
};

export {Locale, LocaleCode};
