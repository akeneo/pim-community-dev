import Family from './Family.interface';
import Attribute from './Attribute.interface';
import Category from './Category.interface';
import Product from './Product.interface';
import Rate, {
  MAX_RATE,
  RANK_1,
  RANK_2,
  RANK_3,
  RANK_4,
  RANK_5,
  RANK_1_COLOR,
  RANK_2_COLOR,
  RANK_3_COLOR,
  RANK_4_COLOR,
  RANK_5_COLOR,
  NO_RATE_COLOR,
} from './Rate.interface';

import Axis, {AxesCollection} from './Axis.interface';
import Rates from './Rates.interface';
import AttributeWithRecommendation from './AttributeWithRecommendation.interface';
import Evaluation, {ProductEvaluation, AxisEvaluation, CriterionEvaluationResult, Status} from './Evaluation.interface';
import {KeyIndicator, keyIndicatorMap, KeyIndicatorTips, KeyIndicatorsTips, Tip} from './KeyIndicator';
import {ProductQualityScore} from './ProductQualityScore';
export * from './KeyIndicator';

export {
  Family,
  Attribute,
  Category,
  Rate,
  Product,
  MAX_RATE,
  RANK_1,
  RANK_2,
  RANK_3,
  RANK_4,
  RANK_5,
  RANK_1_COLOR,
  RANK_2_COLOR,
  RANK_3_COLOR,
  RANK_4_COLOR,
  RANK_5_COLOR,
  NO_RATE_COLOR,
  Axis,
  AxesCollection,
  Rates,
  AttributeWithRecommendation,
  Evaluation,
  ProductEvaluation,
  AxisEvaluation,
  CriterionEvaluationResult,
  Status,
  KeyIndicator,
  keyIndicatorMap,
  KeyIndicatorTips,
  KeyIndicatorsTips,
  Tip,
  ProductQualityScore,
};
export * from './Score';
