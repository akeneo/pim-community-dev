import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {httpGet} from "./fetch";
import {Locale} from "../models/Locale";

const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  const url = router.generate('pim_enrich_locale_rest_index', { activated: true });
  const response = await httpGet(url);

  return await response.json();
};

const getByCode = async (localeCode: string, router: Router): Promise<Locale | null> => {
  const activatedLocales = await getActivatedLocales(router);

  const result = activatedLocales.find((locale) => {
    return locale.code === localeCode;
  });

  return result === undefined ? null : result;
}

export { getActivatedLocales, getByCode }
