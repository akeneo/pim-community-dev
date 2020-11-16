import React, {Children, cloneElement, FC, ReactElement, ReactNode, useMemo} from 'react';
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
  followAttributeRecommendation?: FollowAttributeRecommendationHandler;
  followAttributesListRecommendation?: FollowAttributesListRecommendationHandler;
}

const getRecommendation = (children: ReactNode | null, type: RecommendationType): ReactElement | undefined => {
  let recommendation = <Recommendation type={type} />;

  Children.forEach(children, child => {
    if (React.isValidElement(child) && child.type === Recommendation && child.props.type === type) {
      recommendation = cloneElement(child);
    }
  });

  return recommendation;
};

const getToImproveRecommendation = (
  children: ReactNode | null,
  criterion: string,
  attributes: string[],
  product: Product,
  axis: string,
  evaluation: Evaluation,
  followAttributeRecommendation: FollowAttributeRecommendationHandler | undefined,
  followAttributesListRecommendation: FollowAttributesListRecommendationHandler | undefined
): ReactElement | undefined => {
  let recommendation: ReactElement | null = null;

  Children.forEach(children, child => {
    if (React.isValidElement(child) && child.type === Recommendation && child.props.type === 'to_improve') {
      recommendation = cloneElement(child);
    }
  });

  if (recommendation !== null) {
    return recommendation;
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

const buildRecommendation = (
  children: ReactNode | null,
  criterionEvaluation: CriterionEvaluationResult,
  evaluation: Evaluation,
  product: Product,
  axis: string,
  followAttributeRecommendation: FollowAttributeRecommendationHandler | undefined,
  followAttributesListRecommendation: FollowAttributesListRecommendationHandler | undefined
): ReactElement => {
  const criterion = criterionEvaluation.code;
  const attributes = criterionEvaluation.improvable_attributes || ([] as string[]);

  if ([CRITERION_ERROR, CRITERION_IN_PROGRESS, CRITERION_NOT_APPLICABLE].includes(criterionEvaluation.status)) {
    return <>{getRecommendation(children, criterionEvaluation.status as RecommendationType)}</>;
  }

  if (isSuccess(criterionEvaluation.rate)) {
    return (
      <div className="CriterionSuccessContainer">
        {getRecommendation(children, 'success')}
        <span className="CriterionSuccessTick" />
      </div>
    );
  }

  return (
    <>
      {getToImproveRecommendation(
        children,
        criterion,
        attributes,
        product,
        axis,
        evaluation,
        followAttributeRecommendation,
        followAttributesListRecommendation
      )}
    </>
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
  followAttributesListRecommendation,
}) => {
  const criterion = code;
  const product = useProduct();
  const family = useProductFamily();
  const isClickable = isFollowingCriterionRecommendationAllowed(criterionEvaluation, family, product);
  const handleFollowingCriterionRecommendation =
    !isClickable || followCriterionRecommendation === undefined
      ? undefined
      : () => {
          followCriterionRecommendation(criterionEvaluation, family, product);
        };

  const recommendation = useMemo(() => {
    return buildRecommendation(
      children,
      criterionEvaluation,
      evaluation,
      product,
      axis,
      followAttributeRecommendation,
      followAttributesListRecommendation
    );
  }, [
    children,
    criterionEvaluation,
    evaluation,
    product,
    axis,
    followAttributeRecommendation,
    followAttributesListRecommendation,
  ]);

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
