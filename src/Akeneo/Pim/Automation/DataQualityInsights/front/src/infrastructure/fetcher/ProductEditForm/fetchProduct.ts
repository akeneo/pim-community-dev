const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_product_rest_get';

const fetchProduct = async (id: string) => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      uuid: id,
    })
  );

  return await response.json();
};

export default fetchProduct;
