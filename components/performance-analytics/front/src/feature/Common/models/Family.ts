import {LocaleCode} from './Locale';

export type FamilyCode = string;

export type Family = {
  code: FamilyCode;
  labels: {
    [locale: LocaleCode]: string;
  };
};

export const getFamilyLabel = (family: Family, localCode: LocaleCode): string => {
  return family.labels && family.labels[localCode] ? family.labels[localCode] : `[${family.code}]`;
};
