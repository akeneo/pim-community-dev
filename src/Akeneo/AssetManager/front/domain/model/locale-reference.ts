import {isString, isNull} from 'akeneoassetmanager/domain/model/utils';

type LocaleReference = string | null;
export default LocaleReference;

export const localeReferenceIsEmpty = (localeReference: LocaleReference): localeReference is null =>
  isNull(localeReference);
export const localeReferenceAreEqual = (first: LocaleReference, second: LocaleReference): boolean => first === second;
export const localeReferenceStringValue = (localeReference: LocaleReference) =>
  isNull(localeReference) ? '' : localeReference;

export const denormalizeLocaleReference = (localeReference: any): LocaleReference => {
  if (!(isString(localeReference) || isNull(localeReference))) {
    throw new Error('A locale reference should be a string or null');
  }

  return localeReference;
};
