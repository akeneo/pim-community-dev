import {AttributeCreateFormApp, AttributeEditFormApp, ProductEditFormApp, ProductModelEditFormApp} from './application';
import fetchProductDataQualityEvaluation from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/ProductEditForm/fetchProductDataQualityEvaluation';
import fetchProductModelEvaluation from '@akeneo-pim-community/data-quality-insights/src/infrastructure/fetcher/ProductEditForm/fetchProductModelEvaluation';

import {
  ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  BACK_LINK_SESSION_STORAGE_KEY,
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME,
} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

import {CriterionEvaluationResult, ProductEvaluation} from './domain';

import {DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID} from '@akeneo-pim-community/data-quality-insights/src';
import {Dictionary} from './application/component/Locale/Dictionary';

export {
  ProductEditFormApp,
  ProductModelEditFormApp,
  ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  PRODUCT_DATA_QUALITY_INSIGHTS_TAB_NAME,
  PRODUCT_MODEL_DATA_QUALITY_INSIGHTS_TAB_NAME,
  fetchProductDataQualityEvaluation,
  fetchProductModelEvaluation,
  ProductEvaluation,
  CriterionEvaluationResult,
  AttributeEditFormApp,
  AttributeCreateFormApp,
  BACK_LINK_SESSION_STORAGE_KEY,
  Dictionary,
};
