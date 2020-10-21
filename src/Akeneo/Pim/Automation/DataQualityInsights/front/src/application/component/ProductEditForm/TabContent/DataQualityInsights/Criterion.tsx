import React, {FC, ReactElement, ReactNode, useMemo} from 'react';
import {Family, MAX_RATE, Product, Rate} from '../../../../../domain';
import Evaluation, {
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import {Recommendation, RecommendationAttributesList, RecommendationType} from './Recommendation';
import {isSimpleProduct} from '../../../../helper/ProductEditForm/Product';
import {useFetchProductFamilyInformation, useProduct} from '../../../../../infrastructure/hooks';

const __ = require('oro/translator');

type FollowCriterionHandler = (criterionEvaluation: CriterionEvaluationResult, family: Family|null, product: Product) => void;

interface CriterionProps {
  code: string;
  criterionEvaluation?: CriterionEvaluationResult;
  axis?: string;
  evaluation?: Evaluation;
  followCriterion?: FollowCriterionHandler;
}

const isSuccess = (rate: Rate) => {
  return rate && rate.value === MAX_RATE;
};

const criterionPlaceholder: CriterionEvaluationResult = {
  rate: {
    value: null,
    rank: null,
  },
  code: '',
  status: 'not_applicable',
  improvable_attributes: []

};
const evaluationPlaceholder: Evaluation = {
  rate: {
    value: null,
    rank: null,
  },
  criteria: [],
};

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

const Criterion: FC<CriterionProps> = ({children, code, criterionEvaluation = criterionPlaceholder, axis = '', evaluation = evaluationPlaceholder, followCriterion}) => {
  const criterion = code;
  const product = useProduct();
  const family = useFetchProductFamilyInformation();
  const isClickable = followCriterion !== undefined;

  const recommendation = useMemo(() => {
    return buildRecommendation(children, criterionEvaluation, evaluation, product, axis);
  }, [criterionEvaluation, children]);

  const rowProps = {
    className: `AknVerticalList-item ${isClickable ? 'AknVerticalList-item--clickable' : ''}`,
    onClick: followCriterion === undefined ? undefined : () => {
      followCriterion(criterionEvaluation, family, product)
    },
  }

  return (
    <li data-testid={"dqiProductEvaluationCriterion"} {...rowProps}>
      <div className={`CriterionMessage ${!isSimpleProduct(product) ? 'CriterionMessage--Variant' : ''}`}>
        <span className="CriterionRecommendationMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>
        {recommendation}
      </div>
    </li>
  );
};

export default Criterion;
export type {FollowCriterionHandler};