import {ProductEditFormApp} from './application';

import {DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID} from './application/component/ProductEditForm/TabContent';
import {DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID} from './application/component/ProductEditForm/Sidebar';
import Rate from './application/component/Rate';

import {
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature
} from './infrastructure/fetcher/data-quality-insights-feature';

import fetchProductDataQualityEvaluation from './infrastructure/fetcher/fetchProductDataQualityEvaluation';

import {
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED
} from './infrastructure/context-provider';

import {
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES
} from "./infrastructure/context-provider/ProductContextProvider";

import {DataQualityOverviewCharts} from "./application/component/DqiDashboard";
import {DataQualityOverviewHeader} from "./application/component/DqiDashboard";
import {DataQualityOverviewChartHeader} from "./application/component/DqiDashboard";

export {
  DataQualityOverviewChartHeader,
  DataQualityOverviewHeader,
  DataQualityOverviewCharts,
  ProductEditFormApp,
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature,
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID,
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  Rate,
  fetchProductDataQualityEvaluation
};
