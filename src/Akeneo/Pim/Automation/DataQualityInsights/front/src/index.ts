import Rate from './application/component/Rate';
import Dashboard from './application/component/Dashboard/Dashboard';
import DashboardHelper from './application/component/Dashboard/DashboardHelper';

import {
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_PRODUCT_SAVED,
  DATA_QUALITY_INSIGHTS_PRODUCT_SAVING,
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  PRODUCT_ATTRIBUTES_TAB_LOADED,
  PRODUCT_ATTRIBUTES_TAB_LOADING,
  PRODUCT_MODEL_LEVEL_CHANGED,
  PRODUCT_TAB_CHANGED,
} from './application/listener';

import ProductEditFormApp from './application/ProductEditFormApp';
import ProductModelEditFormApp from './application/ProductModelEditFormApp';
import {DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID} from './application/component/ProductEditForm/TabContent';
import {DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID} from './application/component/ProductEditForm/Sidebar';

import fetchProductDataQualityEvaluation from './infrastructure/fetcher/ProductEditForm/fetchProductDataQualityEvaluation';
import fetchProductModelEvaluation from './infrastructure/fetcher/ProductEditForm/fetchProductModelEvaluation';

import {CriterionEvaluationResult, ProductEvaluation} from './domain';

import {AttributeGroupDQIActivation} from './application/component/AttributeGroup/AttributeGroupDQIActivation';

export {BackLinkButton} from './application';
export * from './application/constant';

export {
  Rate,
  Dashboard,
  DashboardHelper,
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  PRODUCT_ATTRIBUTES_TAB_LOADED,
  PRODUCT_ATTRIBUTES_TAB_LOADING,
  PRODUCT_TAB_CHANGED,
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_PRODUCT_SAVING,
  DATA_QUALITY_INSIGHTS_PRODUCT_SAVED,
  PRODUCT_MODEL_LEVEL_CHANGED,
  ProductEditFormApp,
  ProductModelEditFormApp,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID,
  fetchProductDataQualityEvaluation,
  fetchProductModelEvaluation,
  ProductEvaluation,
  CriterionEvaluationResult,
  AttributeGroupDQIActivation,
};
