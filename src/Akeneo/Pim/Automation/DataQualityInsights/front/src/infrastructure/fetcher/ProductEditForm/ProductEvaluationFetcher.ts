import {ProductEvaluation} from '../../../domain';

export default interface ProductEvaluationFetcher {
  (productId: number): Promise<ProductEvaluation>;
}
