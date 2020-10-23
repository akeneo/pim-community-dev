import React, {FC, ReactElement, ReactNode, useMemo} from 'react';
import {Product} from '../../../../../domain';
import Evaluation, {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import {Recommendation, RecommendationType, RecommendationWithAttributesList} from './Recommendation';
import {useProduct, useProductFamily} from '../../../../../infrastructure/hooks';
import {criterionPlaceholder, evaluationPlaceholder, isSimpleProduct, isSuccess} from '../../../../helper';
import {
  AllowFollowingCriterionRecommendation,
  allowFollowingCriterionRecommendation as defaultAllowFollowingCriterionRecommendation,
  FollowAttributeRecommendationHandler,
  FollowAttributesListRecommendationHandler,
  followCriterionRecommendation as defaultFollowCriterionRecommendation,
  FollowCriterionRecommendationHandler,
} from '../../../../user-actions';

const translate = require('oro/translator');

interface CriterionProps {
  code: string;
  criterionEvaluation?: CriterionEvaluationResult;
  axis?: string;
  evaluation?: Evaluation;
  isFollowingCriterionRecommendationAllowed?: AllowFollowingCriterionRecommendation;
  followCriterionRecommendation?: FollowCriterionRecommendationHandler;
  followAttributeRecommendation?: FollowAttributeRecommendationHandler,
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler
}

const buildRecommendation = (
  recommendationContent: ReactNode | null,
  criterionEvaluation: CriterionEvaluationResult,
  evaluation: Evaluation,
  product: Product,
  axis: string,
  followAttributeRecommendation: FollowAttributeRecommendationHandler|undefined,
  followAttributesListRecommendation: FollowAttributesListRecommendationHandler|undefined
): ReactElement => {
  const criterion = criterionEvaluation.code;
  const attributes = criterionEvaluation.improvable_attributes || ([] as string[]);

  if (criterionEvaluation.status === CRITERION_ERROR) {
    return <Recommendation type={RecommendationType.ERROR} />;
  } else if (criterionEvaluation.status === CRITERION_IN_PROGRESS) {
    return <Recommendation type={RecommendationType.IN_PROGRESS} />;
  } else if (criterionEvaluation.status === CRITERION_NOT_APPLICABLE) {
    return <Recommendation type={RecommendationType.NOT_APPLICABLE} />;
  } else if (isSuccess(criterionEvaluation.rate) && attributes.length == 0) {
    return (
      <div className="CriterionSuccessContainer">
        <Recommendation type={RecommendationType.SUCCESS} />
        <span className="CriterionSuccessTick" />
      </div>
    );
  }

  if (recommendationContent !== undefined) {
    const element = recommendationContent as ReactElement;
    if (
      element.type === Recommendation &&
      (element.props.supports === undefined || element.props.supports(criterionEvaluation))
    ) {
      return element;
    }
  }

  return (
    <RecommendationWithAttributesList
      criterion={criterion}
      attributes={attributes}
      product={product}
      axis={axis}
      evaluation={evaluation}
      followAttributeRecommendation={followAttributeRecommendation}
      followAttributesListRecommendation={followAttributesListRecommendation}
    />
  );
};

const Criterion: FC<CriterionProps> = ({
  children,
  code,
  criterionEvaluation = criterionPlaceholder,
  axis = '',
  evaluation = evaluationPlaceholder,
  followCriterionRecommendation = defaultFollowCriterionRecommendation,
  isFollowingCriterionRecommendationAllowed = defaultAllowFollowingCriterionRecommendation,
  followAttributeRecommendation,
  followAttributesListRecommendation
}) => {
  const criterion = code;
  const product = useProduct();
  const family = useProductFamily();
  const isClickable = isFollowingCriterionRecommendationAllowed(criterionEvaluation, family, product);
  const handleFollowingCriterionRecommendation = (!isClickable || followCriterionRecommendation === undefined) ? undefined : () => {
    followCriterionRecommendation(criterionEvaluation, family, product);
  };

  const recommendation = useMemo(() => {
    return buildRecommendation(children, criterionEvaluation, evaluation, product, axis, followAttributeRecommendation, followAttributesListRecommendation);
  }, [children, criterionEvaluation, evaluation, product, axis, followAttributeRecommendation, followAttributesListRecommendation]);

  const rowProps = {
    className: `AknVerticalList-item ${isClickable ? 'AknVerticalList-item--clickable' : ''}`,
    onClick: handleFollowingCriterionRecommendation,
  };

  return (
    <li data-testid={'dqiProductEvaluationCriterion'} {...rowProps}>
      <div className={`CriterionMessage ${!isSimpleProduct(product) ? 'CriterionMessage--Variant' : ''}`}>
        <span className="CriterionRecommendationMessage">
          {translate(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>
        {recommendation}
      </div>
    </li>
  );
};

export default Criterion;
