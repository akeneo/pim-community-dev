import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {httpGet} from "./fetch";
import {Locale} from "../models/Locale";

let cacheActivatedLocales: Locale[];

const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  if (!cacheActivatedLocales) {
    const url = router.generate('pim_enrich_locale_rest_index', { activated: true });
    const response = await httpGet(url);
    cacheActivatedLocales = await response.json();
  }

  return cacheActivatedLocales;
};

const getActivatedLocaleByCode = async (localeCode: string, router: Router): Promise<Locale | null> => {
  const activatedLocales = await getActivatedLocales(router);

  const result = activatedLocales.find((locale) => {
    return locale.code === localeCode;
  });

  return result === undefined ? null : result;
}

const checkLocaleExists = async (localeCode: string|null, router: Router): Promise<boolean> => {
  if (!localeCode) {
    return true;
  }

  if (null === await getActivatedLocaleByCode(localeCode, router)) {
    console.error(`The ${localeCode} locale code does not exist or is not activated`);

    return false;
  }

  return true;
}

export { getActivatedLocales, getActivatedLocaleByCode, checkLocaleExists }
