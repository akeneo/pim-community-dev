import {QualityScoreModel} from '../../../domain';
import {ProductType} from '../../../domain/Product.interface';

const Routing = require('routing');

const ROUTE_NAMES = {
  product: 'akeneo_data_quality_insights_product_quality_score',
  product_model: 'akeneo_data_quality_insights_product_model_quality_score',
};

export type Payload =
  | {
      evaluations_available: false;
    }
  | {
      evaluations_available: true;
      scores: QualityScoreModel;
    };

const fetchQualityScore = async (type: ProductType, id: string): Promise<Payload> => {
  const response = await fetch(Routing.generate(ROUTE_NAMES[type], {productId: id}));
  return response.json();
};

export {fetchQualityScore};
