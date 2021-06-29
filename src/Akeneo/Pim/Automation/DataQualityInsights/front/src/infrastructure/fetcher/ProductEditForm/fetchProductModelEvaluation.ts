import {ProductEvaluation} from '../../../domain';
import ProductEvaluationFetcher from './ProductEvaluationFetcher';

const Routing = require('routing');

const ROUTE_NAME = 'akeneo_data_quality_insights_product_model_evaluation';

const fetchProductModelEvaluation: ProductEvaluationFetcher = async (productId: number): Promise<ProductEvaluation> => {
  const response = await fetch(
    Routing.generate(ROUTE_NAME, {
      productId: productId,
    })
  );

  return await response.json();
};

export default fetchProductModelEvaluation;
