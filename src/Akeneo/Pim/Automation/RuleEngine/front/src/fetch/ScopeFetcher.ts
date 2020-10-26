import {Router} from '../dependenciesTools';
import {httpGet} from './fetch';
import {Scope} from '../models';

const fetchAllScopes = async (router: Router): Promise<Scope[]> => {
  const url = router.generate('pim_enrich_channel_rest_index');
  const response = await httpGet(url);
  return await response.json();
};

export {fetchAllScopes};
