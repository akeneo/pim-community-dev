import {Router} from '../dependenciesTools';
import {httpPost} from './fetch';
import {Category, CategoryCode} from '../models/Category';

const fetchCategoriesByIdentifiers = async (
  categoryCodes: CategoryCode[],
  router: Router
): Promise<Category[]> => {
  const url = router.generate('pimee_enrich_rule_definition_get_categories');
  const response = await httpPost(url, {
    body: {identifiers: categoryCodes},
  });

  return response.status === 404 ? null : await response.json();
};

export {fetchCategoriesByIdentifiers};
