import {httpGet} from './fetch';
import {Family} from '../models';
import {Router} from '../dependenciesTools';

type IndexedFamilies = {[familyCode: string]: Family};

const fetchFamiliesByIdentifiers = async (
  familyIdentifiers: string[],
  router: Router
): Promise<IndexedFamilies> => {
  const url = router.generate('pim_enrich_family_rest_index', {
    identifiers: familyIdentifiers.join(','),
    options: {
      expanded: 0,
    },
  });
  const response = await httpGet(url);

  return response.status === 404 ? null : await response.json();
};

export {fetchFamiliesByIdentifiers, IndexedFamilies};
