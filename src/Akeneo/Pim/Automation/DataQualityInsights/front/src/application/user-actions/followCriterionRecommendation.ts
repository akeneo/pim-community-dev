import {CriterionEvaluationResult, Family, Product} from '../../domain';

type FollowCriterionRecommendationHandler = (
  criterionEvaluation: CriterionEvaluationResult,
  family: Family | null,
  product: Product
) => void;

type AllowFollowingCriterionRecommendation = (
  criterionEvaluation: CriterionEvaluationResult,
  family: Family | null,
  product: Product
) => boolean;

const followCriterionRecommendation: FollowCriterionRecommendationHandler = () => {};
const allowFollowingCriterionRecommendation: AllowFollowingCriterionRecommendation = () => false;

export {followCriterionRecommendation, allowFollowingCriterionRecommendation};
export type {FollowCriterionRecommendationHandler, AllowFollowingCriterionRecommendation};
