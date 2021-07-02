import {fetchActivatedLocales, fetchLocales} from '../fetchers/LocaleFetcher';
import {Locale, LocaleCode, Router} from '@akeneo-pim-community/shared';

let cacheActivatedLocales: Locale[];
let cachedLocales: Locale[];

const getLocale = async (router: Router, code: LocaleCode): Promise<Locale | undefined> => {
  if (!cachedLocales) {
    cachedLocales = await fetchLocales(router);
    return new Promise(resolve => resolve(cachedLocales.find(locale => locale.code === code)));
  }
  return new Promise(resolve => resolve(cachedLocales.find(locale => locale.code === code)));
};

const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  if (!cacheActivatedLocales) {
    cacheActivatedLocales = await fetchActivatedLocales(router);
    return new Promise(resolve => resolve(cacheActivatedLocales));
  }
  return new Promise(resolve => resolve(cacheActivatedLocales));
};

export {getActivatedLocales, getLocale};
