const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_categorytree_children';

const fetchCategoryChildren = async (locale: string, categoryId: string) => {
  const response = await fetch(Routing.generate(ROUTE_NAME, {
    _format: 'json',
    dataLocale: locale,
    context: 'associate',
    id: categoryId,
    include_parent: false
  }));

  return await response.json();
};

export default fetchCategoryChildren;
