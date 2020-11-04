const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_product_model_rest_get';

const fetchProductModel = async (id: number) => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      id: id,
    })
  );

  return await response.json();
};

export default fetchProductModel;
