const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_model_quality_score';

const fetchProductModelQualityScore = async (productId: number) => {
  const response = await fetch(Routing.generate(ROUTE_NAME, {productId}));
  return response.json();
};

export {fetchProductModelQualityScore};
