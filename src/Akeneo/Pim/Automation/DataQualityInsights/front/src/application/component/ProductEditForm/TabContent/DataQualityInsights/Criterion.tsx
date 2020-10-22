import React, {FC, ReactElement, ReactNode, useMemo} from 'react';
import {Family, Product} from '../../../../../domain';
import Evaluation, {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import {Recommendation, RecommendationAttributesList, RecommendationType} from './Recommendation';
import {useFetchProductFamilyInformation, useProduct} from '../../../../../infrastructure/hooks';
import {criterionPlaceholder, evaluationPlaceholder, isSimpleProduct, isSuccess} from '../../../../helper';

const translate = require('oro/translator');

type FollowCriterionHandler = (criterionEvaluation: CriterionEvaluationResult, family: Family|null, product: Product) => void;
type CheckFollowingCriterionActive = (criterionEvaluation: CriterionEvaluationResult, family: Family|null, product: Product) => boolean;

interface CriterionProps {
  code: string;
  criterionEvaluation?: CriterionEvaluationResult;
  axis?: string;
  evaluation?: Evaluation;
  follow?: FollowCriterionHandler;
  isFollowingActive?: CheckFollowingCriterionActive;
}

const buildRecommendation = (recommendationContent: ReactNode|null, criterionEvaluation: CriterionEvaluationResult, evaluation: Evaluation, product: Product, axis: string): ReactElement => {
  const criterion = criterionEvaluation.code;
  const attributes = criterionEvaluation.improvable_attributes || [] as string[];

  if(criterionEvaluation.status === CRITERION_ERROR) {
    return (<Recommendation type={RecommendationType.ERROR} />);
  } else if(criterionEvaluation.status === CRITERION_IN_PROGRESS) {
    return (<Recommendation type={RecommendationType.IN_PROGRESS} />);
  } else if(criterionEvaluation.status === CRITERION_NOT_APPLICABLE) {
    return (<Recommendation type={RecommendationType.NOT_APPLICABLE} />);
  } else if(isSuccess(criterionEvaluation.rate) && attributes.length == 0) {
    return (<Recommendation type={RecommendationType.SUCCESS} />);
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
    <RecommendationAttributesList criterion={criterion} attributes={attributes} product={product} axis={axis} evaluation={evaluation}/>
  );
};

const Criterion: FC<CriterionProps> = ({children, code, criterionEvaluation = criterionPlaceholder, axis = '', evaluation = evaluationPlaceholder, follow, isFollowingActive = () => false}) => {
  const criterion = code;
  const product = useProduct();
  const family = useFetchProductFamilyInformation();
  const isClickable = isFollowingActive(criterionEvaluation, family, product) && (follow !== undefined);
  const handleFollowingCriterion = (!isClickable || follow === undefined) ? undefined : () => {
    follow(criterionEvaluation, family, product)
  };

  const recommendation = useMemo(() => {
    return buildRecommendation(children, criterionEvaluation, evaluation, product, axis);
  }, [criterionEvaluation, children]);

  const rowProps = {
    className: `AknVerticalList-item ${isClickable ? 'AknVerticalList-item--clickable' : ''}`,
    onClick: handleFollowingCriterion,
  }

  return (
    <li data-testid={"dqiProductEvaluationCriterion"} {...rowProps}>
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
export type {FollowCriterionHandler, CheckFollowingCriterionActive};