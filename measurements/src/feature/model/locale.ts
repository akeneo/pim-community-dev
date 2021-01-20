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

export type {LocaleCode, Locale};
