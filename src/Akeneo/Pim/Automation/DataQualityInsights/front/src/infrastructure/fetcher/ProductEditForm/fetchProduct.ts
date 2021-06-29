const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_product_rest_get';

const fetchProduct = async (id: number) => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      id: id,
    })
  );

  return await response.json();
};

export default fetchProduct;
