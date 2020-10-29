import {Router} from '../dependenciesTools';
import {httpGet} from './fetch';
import {Currency} from '../models/Currency';

const fetchAllCurrencies = async (
  router: Router
): Promise<{[currencyCode: string]: Currency}> => {
  const url = router.generate('pim_enrich_currency_rest_index');
  const response = await httpGet(url);

  return await response.json();
};

export {fetchAllCurrencies};
