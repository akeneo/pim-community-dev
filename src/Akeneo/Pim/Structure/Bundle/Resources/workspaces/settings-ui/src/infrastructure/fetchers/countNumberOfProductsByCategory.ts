const Routing = require('routing');

const useCountNumberOfProductsByCategory = async (categoryId: number): Promise<number> => {
  const response = await fetch(Routing.generate('pim_enrich_categorytree_count_category_products', {id: categoryId}));

  return await response.json();
};

export {useCountNumberOfProductsByCategory};
