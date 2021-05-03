const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_categorytree_remove';

const deleteCategory = async (categoryId: number): Promise<boolean> => {
  const response = await fetch(Routing.generate(ROUTE_NAME, {id: categoryId}), {
    method: 'DELETE',
    headers: [['X-Requested-With', 'XMLHttpRequest']],
  });

  return response.ok;
};

export {deleteCategory};
