import {TimeToEnrichFilters} from '../TimeToEnrich';

export enum Metric {
  TIME_TO_ENRICH = 'time_to_enrich',
}

export const Metrics = [Metric.TIME_TO_ENRICH];

export enum Aggregation {
  FAMILIES = 'families',
  CATEGORIES = 'categories',
}

export const Aggregations = [Aggregation.FAMILIES, Aggregation.CATEGORIES];

export enum PredefinedPeriod {
  LAST_MONTH = 'last_month',
  LAST_12_WEEKS = 'last_12_weeks',
  LAST_12_MONTHS = 'last_12_months',
}

export const PredefinedPeriods = [
  PredefinedPeriod.LAST_MONTH,
  PredefinedPeriod.LAST_12_WEEKS,
  PredefinedPeriod.LAST_12_MONTHS,
];

export enum PredefinedComparison {
  SAME_PERIOD_LAST_YEAR = 'same_period_last_year',
  SAME_PERIOD_JUST_BEFORE = 'same_period_just_before',
}

export const PredefinedComparisons = [
  PredefinedComparison.SAME_PERIOD_LAST_YEAR,
  PredefinedComparison.SAME_PERIOD_JUST_BEFORE,
];

export const defaultFilters: TimeToEnrichFilters = {
  metric: Metric.TIME_TO_ENRICH,
  period: PredefinedPeriod.LAST_MONTH,
  aggregation: Aggregation.FAMILIES,
  comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
  families: [],
  channels: [],
  locales: [],
};
