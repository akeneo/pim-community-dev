const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_axis_rates';

const fetchProductAxisRates = async (productId: number) => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      productId: productId,
    })
  );

  return await response.json();
};

export default fetchProductAxisRates;
