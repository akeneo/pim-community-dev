import {ProductEvaluation} from "../../domain";

const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_evaluation';

const fetchProductDataQualityEvaluation = async (productId: number): Promise<ProductEvaluation> => {
  const response = await fetch(Routing.generate(ROUTE_NAME, {
    productId: productId
  }));

  return await response.json();
};

export default fetchProductDataQualityEvaluation;
