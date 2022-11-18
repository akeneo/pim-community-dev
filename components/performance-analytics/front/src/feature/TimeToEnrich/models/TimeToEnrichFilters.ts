import {Aggregation, Metric, PredefinedComparison, PredefinedPeriod} from '../../Common';

type TimeToEnrichFilters = {
  metric: Metric;
  period: PredefinedPeriod;
  aggregation: Aggregation;
  comparison: PredefinedComparison;
  families: string[];
  channels: string[];
  locales: string[];
};

const getStartDate: (filters: TimeToEnrichFilters) => string = filters => {
  let date = new Date();
  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    date.setFullYear(date.getFullYear() - 1);

    return date.toISOString().substr(0, 10);
  }

  date.setDate(date.getDate() - 12 * 7);
  return date.toISOString().substr(0, 10);
};

const getEndDate: (filters: TimeToEnrichFilters) => string = filters => {
  let date = new Date();
  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    return date.toISOString().substr(0, 10);
  }

  return date.toISOString().substr(0, 10);
};

const getPeriodType: (filters: TimeToEnrichFilters) => string = filters => {
  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    return 'month';
  }

  return 'week';
};

export type {TimeToEnrichFilters};
export {getStartDate, getEndDate, getPeriodType};
