import {ProductEvaluation} from '../../../domain';

export default interface ProductEvaluationFetcher {
  (entityId: string | number): Promise<ProductEvaluation>;
}
