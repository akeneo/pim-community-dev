import {Aggregation, Metric, PredefinedComparison, PredefinedPeriod} from '../../Common';

export type TimeToEnrichFilters = {
  metric: Metric;
  period: PredefinedPeriod;
  aggregation: Aggregation;
  comparison: PredefinedComparison;
  families: string[];
  channels: string[];
  locales: string[];
};

export type TimeToEnrichEntityType = 'family' | 'category';

const getFirstDayOfTheWeek = (date: Date): Date => {
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? -6 : 1);

  return new Date(date.setDate(diff));
};

const getFirstDayOfTheMonth = (date: Date): Date => {
  return new Date(date.getFullYear(), date.getMonth(), 1, date.getHours());
};

const getStartDate: (filters: TimeToEnrichFilters) => string = filters => {
  let date = new Date();

  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    date.setFullYear(date.getFullYear() - 1);
    return getFirstDayOfTheMonth(date).toISOString().substr(0, 10);
  }

  date.setDate(date.getDate() - 12 * 7);
  return getFirstDayOfTheWeek(date).toISOString().substr(0, 10);
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

export {getStartDate, getEndDate, getPeriodType};
