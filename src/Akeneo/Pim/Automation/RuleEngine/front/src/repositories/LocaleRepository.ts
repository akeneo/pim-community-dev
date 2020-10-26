import {Router} from '../dependenciesTools';
import {Locale} from '../models';
import {fetchActivatedLocales} from '../fetch/LocaleFetcher';

let cacheActivatedLocales: Locale[];

const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  if (!cacheActivatedLocales) {
    cacheActivatedLocales = await fetchActivatedLocales(router);
  }

  return cacheActivatedLocales;
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

export {getActivatedLocales, getActivatedLocaleByCode, checkLocaleExists};
