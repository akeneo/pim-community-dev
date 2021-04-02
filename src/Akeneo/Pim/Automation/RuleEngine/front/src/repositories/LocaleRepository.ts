import {Router} from '../dependenciesTools';
import {Locale} from '../models';
import {fetchActivatedLocales, fetchUiLocales} from '../fetch/LocaleFetcher';

let cacheActivatedLocales: Locale[];
let cacheUiLocales: Locale[];

const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  if (!cacheActivatedLocales) {
    cacheActivatedLocales = await fetchActivatedLocales(router);
  }

  return cacheActivatedLocales;
};

const getUiLocales = async (router: Router): Promise<Locale[]> => {
  if (!cacheUiLocales) {
    cacheUiLocales = await fetchUiLocales(router);
  }

  return cacheUiLocales;
};

const getActivatedLocaleByCode = async (
  localeCode: string,
  router: Router
): Promise<Locale | null> => {
  const activatedLocales = await fetchActivatedLocales(router);

  const result = activatedLocales.find(locale => {
    return locale.code === localeCode;
  });

  return result === undefined ? null : result;
};

const checkLocaleExists = async (
  localeCode: string | null,
  router: Router
): Promise<boolean> => {
  if (!localeCode) {
    return true;
  }

  return null !== (await getActivatedLocaleByCode(localeCode, router));
};

export {
  getActivatedLocales,
  getUiLocales,
  getActivatedLocaleByCode,
  checkLocaleExists,
};
