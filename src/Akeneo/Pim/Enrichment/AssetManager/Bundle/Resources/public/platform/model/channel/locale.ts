export type LocaleCode = string;
export type Locale = {
  code: LocaleCode;
  label: string;
  region: string;
  language: string;
};

export type LocaleReference = LocaleCode | null;
