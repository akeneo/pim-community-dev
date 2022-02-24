import {Locale, LocaleCode, Router} from '@akeneo-pim-community/shared';
import {LocaleFetcher} from '../fetchers';

let cacheActivatedLocales: Locale[] | undefined;
let cachedLocales: Locale[] | undefined;

const getLocale = async (router: Router, code: LocaleCode): Promise<Locale | undefined> => {
  if (!cachedLocales) {
    cachedLocales = await LocaleFetcher.fetchAll(router);
    return new Promise(resolve => resolve((cachedLocales || []).find(locale => locale.code === code)));
  }
  return new Promise(resolve => resolve((cachedLocales || []).find(locale => locale.code === code)));
};

const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  if (!cacheActivatedLocales) {
    cacheActivatedLocales = await LocaleFetcher.fetchActivated(router);
    return new Promise(resolve => resolve(cacheActivatedLocales || []));
  }
  return new Promise(resolve => resolve(cacheActivatedLocales || []));
};

const clearCache = () => {
  cachedLocales = undefined;
  cacheActivatedLocales = undefined;
}

const LocaleRepository = {
  find: getLocale,
  findActivated: getActivatedLocales,
  clearCache,
};

export {LocaleRepository};
