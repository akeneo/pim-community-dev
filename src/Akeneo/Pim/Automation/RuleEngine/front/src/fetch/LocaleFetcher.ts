import {Router} from '../dependenciesTools';
import {httpGet} from './fetch';
import {Locale} from '../models';

const fetchActivatedLocales = async (router: Router): Promise<Locale[]> => {
  const url = router.generate('pim_enrich_locale_rest_index', {
    activated: true,
  });
  const response = await httpGet(url);

  return await response.json();
};

export {fetchActivatedLocales};
