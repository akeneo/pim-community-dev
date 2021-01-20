declare type LocaleCode = string;
declare type LocaleLabel = string;
declare type LocaleRegion = string;
declare type LocaleLanguage = string;
declare type Locale = {
    code: LocaleCode;
    label: LocaleLabel;
    region: LocaleRegion;
    language: LocaleLanguage;
};
declare const denormalizeLocale: (locale: any) => Locale;
declare const isLocales: (locales: any) => locales is Locale[];
declare const isLocale: (locale: any) => locale is Locale;
declare const createLocaleFromCode: (code: LocaleCode) => Locale;
declare const localeExists: (locales: Locale[], currentLocale: LocaleCode) => boolean;
export { createLocaleFromCode, denormalizeLocale, localeExists, isLocales, isLocale };
export type { Locale, LocaleCode, LocaleLabel, LocaleRegion, LocaleLanguage };
