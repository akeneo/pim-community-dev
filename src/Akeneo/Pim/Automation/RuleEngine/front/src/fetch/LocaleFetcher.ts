import {Router} from "../dependenciesTools/provider/applicationDependenciesProvider.type";
import {httpGet} from "./fetch";
import {Locale} from "../models/Locale";

export const getActivatedLocales = async (router: Router): Promise<Locale[]> => {
  const url = router.generate('pim_enrich_locale_rest_index', { activated: true });
  const response = await httpGet(url);

  return await response.json();
};
