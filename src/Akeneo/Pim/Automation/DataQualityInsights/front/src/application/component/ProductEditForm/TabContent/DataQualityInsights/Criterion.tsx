import React, {FunctionComponent} from 'react';

import RecommendationAttributesList from './RecommendationAttributesList';
import {MAX_RATE, Rate} from '../../../../../domain';
import Evaluation, {
  CRITERION_DONE,
  CRITERION_ERROR,
  CRITERION_IN_PROGRESS,
  CRITERION_NOT_APPLICABLE,
  CriterionEvaluationResult,
} from '../../../../../domain/Evaluation.interface';
import {useFetchProductFamilyInformation, useProduct} from '../../../../../infrastructure/hooks';
import {isProductModel, isSimpleProduct} from '../../../../helper/ProductEditForm/Product';
import {
  ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE,
  ATTRIBUTE_SPELLING_CRITERION_CODE,
  BACK_LINK_SESSION_STORAGE_KEY,
} from '../../../../constant';
import {
  redirectToAttributeGridFilteredByFamilyAndQuality,
  redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes,
} from '../../../../../infrastructure/AttributeGridRouter';

const __ = require('oro/translator');

interface CriterionProps {
  criterionEvaluation: CriterionEvaluationResult;
  axis: string;
  evaluation: Evaluation;
}

const isSuccess = (rate: Rate) => {
  return rate && rate.value === MAX_RATE;
};

const Criterion: FunctionComponent<CriterionProps> = ({criterionEvaluation, axis, evaluation}) => {
  const product = useProduct();
  const family = useFetchProductFamilyInformation();

  const criterion = criterionEvaluation.code;
  const attributes = criterionEvaluation.improvable_attributes || ([] as string[]);

  let criterionContent: any;
  if (criterionEvaluation.status === CRITERION_ERROR) {
    criterionContent = (
      <span className="CriterionErrorMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.error.criterion_error`)}
      </span>
    );
  } else if (criterionEvaluation.status === CRITERION_IN_PROGRESS) {
    criterionContent = (
      <span className="CriterionInProgressMessage">
        {__(`akeneo_data_quality_insights.product_evaluation.messages.grading_in_progress`)}
      </span>
    );
  } else if (criterionEvaluation.status === CRITERION_NOT_APPLICABLE) {
    criterionContent = <span className="NotApplicableAttribute">N/A</span>;
  } else if (isSuccess(criterionEvaluation.rate) && attributes.length == 0) {
    criterionContent = (
      <div className="CriterionSuccessContainer">
        <span className="CriterionSuccessMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.messages.success.criterion`)}
        </span>
        <span className="CriterionSuccessTick" />
      </div>
    );
  } else {
    criterionContent = (
      <RecommendationAttributesList
        criterion={criterion}
        attributes={attributes}
        product={product}
        axis={axis}
        evaluation={evaluation}
      />
    );
  }

  const isCriterionClickable = () => {
    return (
      [ATTRIBUTE_SPELLING_CRITERION_CODE, ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE].includes(criterion) &&
      criterionEvaluation.status === CRITERION_DONE &&
      !isSuccess(criterionEvaluation.rate)
    );
  };
  const redirectToAttributeGrid = () => {
    if (family) {
      window.sessionStorage.setItem(
        BACK_LINK_SESSION_STORAGE_KEY,
        JSON.stringify({
          label: __('akeneo_data_quality_insights.product_edit_form.back_to_products'),
          route: isProductModel(product) ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit',
          routeParams: {id: product.meta.id},
          displayLinkRoutes: ['pim_enrich_attribute_index', 'pim_enrich_attribute_edit'],
        })
      );
      if (criterion === ATTRIBUTE_OPTION_SPELLING_CRITERION_CODE) {
        redirectToAttributeGridFilteredByFamilyAndQualityAndSelectAttributeTypes(family.meta.id);
      } else {
        redirectToAttributeGridFilteredByFamilyAndQuality(family.meta.id);
      }
    }
  };

  const className = `AknVerticalList-item ${isCriterionClickable() ? 'AknVerticalList-item--clickable' : ''}`;

  return (
    <li
      className={className}
      onClick={() => (isCriterionClickable() ? redirectToAttributeGrid() : null)}
      data-testid={'dqiProductEvaluationCriterion'}
    >
      <div className={`CriterionMessage ${!isSimpleProduct(product) ? 'CriterionMessage--Variant' : ''}`}>
        <span className="CriterionRecommendationMessage">
          {__(`akeneo_data_quality_insights.product_evaluation.criteria.${criterion}.recommendation`)}:&nbsp;
        </span>

        {criterionContent}
      </div>
    </li>
  );
};

export default Criterion;
