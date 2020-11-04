// eslint-disable-next-line @typescript-eslint/no-var-requires
const FetcherRegistry = require('pim/fetcher-registry');
import {Router} from '../dependenciesTools';
import {generateUrl} from '../dependenciesTools/hooks';
import {httpGet} from './fetch';

const fetchRootCategoryTrees = async () =>
  await FetcherRegistry.getFetcher('category').fetchAll();

const fetchCategoryTree = async (
  router: Router,
  selectedCategoriesIds: number[],
  categoryTreeId: number
) => {
  const url = generateUrl(
    router,
    'pimee_enrich_rule_definition_get_category_tree',
    {
      categoryTreeId: categoryTreeId,
      'selected[]': selectedCategoriesIds,
    }
  );
  return await httpGet(url);
};

const fetchCategoryTreeChildren = async (
  router: Router,
  locale: string,
  categoryId: number
) => {
  const url = generateUrl(router, 'pim_enrich_categorytree_children', {
    _format: 'json',
    context: 'associate',
    dataLocale: locale,
    id: categoryId,
    include_parent: false,
  });
  return await httpGet(url);
};

export {fetchCategoryTree, fetchCategoryTreeChildren, fetchRootCategoryTrees};
