import {ProductEditFormApp} from './application';

import {DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID} from './application/component/ProductEditForm/TabContent';
import {DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID} from './application/component/ProductEditForm/Sidebar';
import Rate from './application/component/Rate';

import {
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature
} from './infrastructure/fetcher/data-quality-insights-feature';

import {
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED
} from './infrastructure/context-provider';

export {
  ProductEditFormApp,
  DataQualityInsightsFeature,
  getDataQualityInsightsFeature,
  CATALOG_CONTEXT_CHANNEL_CHANGED,
  CATALOG_CONTEXT_LOCALE_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_VISIBILITY_CHANGED,
  DATA_QUALITY_INSIGHTS_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  DATA_QUALITY_INSIGHTS_AXIS_RATES_OVERVIEW_SIDEBAR_CONTAINER_ELEMENT_ID,
  Rate
};
