import React, {FC, ReactElement, ReactNode, useMemo} from 'react';
import {MAX_RATE, Product, Rate} from '../../../../../domain';
import Evaluation, {
  CRITERION_DONE,
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import {Recommendation, RecommendationAttributesList, RecommendationType} from './Recommendation';
import {
  ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE,
  ATTRIBUTE_SPELLING_CRITERION_CODE,
  BACK_LINK_SESSION_STORAGE_KEY,
} from '../../../../constant';
import {isProductModel, isSimpleProduct} from '../../../../helper/ProductEditForm/Product';
import {useFetchProductFamilyInformation, useProduct} from '../../../../../infrastructure/hooks';
import {
  redirectToAttributeGridFilteredByFamilyAndQuality,
  redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes,
} from '../../../../../infrastructure/AttributeGridRouter';

const __ = require('oro/translator');

interface CriterionProps {
  code: string;
  criterionEvaluation?: CriterionEvaluationResult;
  axis?: string;
  evaluation?: Evaluation;
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

const Criterion: FC<CriterionProps> = ({children, code, criterionEvaluation = criterionPlaceholder, axis = '', evaluation = evaluationPlaceholder}) => {
  const criterion = code;
  const product = useProduct();
  const family = useFetchProductFamilyInformation();

  const recommendation = useMemo(() => {
    return buildRecommendation(children, criterionEvaluation, evaluation, product, axis);
  }, [criterionEvaluation, children]);

  // @todo[DAPI-1339] move to EE
  const isCriterionClickable = () => {
    return (
      [ATTRIBUTE_SPELLING_CRITERION_CODE, ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE].includes(criterion) &&
      criterionEvaluation.status === CRITERION_DONE &&
      !isSuccess(criterionEvaluation.rate)
    );
  };

  // @todo[DAPI-1339] move to EE
  const redirectToAttributeGrid = () => {
      if (family) {
          window.sessionStorage.setItem(BACK_LINK_SESSION_STORAGE_KEY, JSON.stringify({
            label: __('akeneo_data_quality_insights.product_edit_form.back_to_products'),
            route: isProductModel(product) ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit',
            routeParams: {id: product.meta.id},
            displayLinkRoutes: [
              'pim_enrich_attribute_index',
              'pim_enrich_attribute_edit',

            ],
          }));
        if (criterion === ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE) {
          redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes(family.meta.id);
        } else {
          redirectToAttributeGridFilteredByFamilyAndQuality(family.meta.id);
        }
      }
  };

  const className = `AknVerticalList-item ${isCriterionClickable() ? 'AknVerticalList-item--clickable' : ''}`;

  return (
    <li className={className} onClick={() => isCriterionClickable() ? redirectToAttributeGrid() : null} data-testid={"dqiProductEvaluationCriterion"}>
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
