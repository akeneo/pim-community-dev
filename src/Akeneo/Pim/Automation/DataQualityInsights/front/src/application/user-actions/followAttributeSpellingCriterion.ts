import {redirectToAttributeGridFilteredByFamilyAndQuality} from '@akeneo-pim-ee/data-quality-insights/src/infrastructure/navigation/AttributeGridRouter';
import {
  CriterionEvaluationResult,
  Family,
  MAX_RATE,
  Product,
} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {CRITERION_DONE} from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import {isProductModel} from '@akeneo-pim-community/data-quality-insights/src/application/helper';
import {BACK_LINK_SESSION_STORAGE_KEY} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

const translate = require('oro/translator');

const followAttributeSpellingCriterion = (
  criterionEvaluation: CriterionEvaluationResult,
  family: Family | null,
  product: Product,
  locale: string
) => {
  if (family === null || criterionEvaluation.status !== CRITERION_DONE || criterionEvaluation.rate.value === MAX_RATE) {
    return;
  }
  window.sessionStorage.setItem(
    BACK_LINK_SESSION_STORAGE_KEY,
    JSON.stringify({
      label: translate('akeneo_data_quality_insights.product_edit_form.back_to_products'),
      route: isProductModel(product) ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit',
      routeParams: {id: product.meta.id},
      displayLinkRoutes: ['pim_enrich_attribute_index', 'pim_enrich_attribute_edit'],
    })
  );
  redirectToAttributeGridFilteredByFamilyAndQuality(family.code, locale);
};

const checkFollowingAttributeSpellingCriterionActive = (criterionEvaluation: CriterionEvaluationResult) => {
  return (
    criterionEvaluation.code === 'consistency_attribute_spelling' &&
    criterionEvaluation.status === CRITERION_DONE &&
    criterionEvaluation.rate.value !== MAX_RATE
  );
};

export {followAttributeSpellingCriterion, checkFollowingAttributeSpellingCriterionActive};
