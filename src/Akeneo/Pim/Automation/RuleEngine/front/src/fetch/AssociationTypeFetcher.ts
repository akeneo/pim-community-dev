import { Router } from '../dependenciesTools';
import { httpGet } from './fetch';
import { AssociationType } from '../models';

const fetchAllAssociationTypes = async (
  router: Router
): Promise<AssociationType[]> => {
  const url = router.generate('pim_enrich_associationtype_rest_index');
  const response = await httpGet(url);

  return await response.json();
};

export { fetchAllAssociationTypes };
