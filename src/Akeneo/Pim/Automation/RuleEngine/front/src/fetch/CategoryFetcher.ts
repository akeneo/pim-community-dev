import {Router} from '../dependenciesTools';
import {httpGet} from './fetch';
import {Category, CategoryCode} from '../models/Category';

const fetchCategoriesByIdentifiers = async (
  categoryCodes: CategoryCode[],
  router: Router
): Promise<Category[]> => {
  const url = router.generate('pimee_enrich_rule_definition_get_categories', {
    identifiers: categoryCodes,
  });
  const response = await httpGet(url);

  return response.status === 404 ? null : await response.json();
};

export {fetchCategoriesByIdentifiers};
