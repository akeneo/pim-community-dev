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

const getLastDayOfTheWeek = (date: Date): Date => {
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? 0 : 7);
  return new Date(date.setDate(diff));
};

const getLastDayOfThePreviousWeek = (date: Date): Date => {
  const previousWeek = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 7, date.getHours());
  return getLastDayOfTheWeek(previousWeek);
};

const getFirstDayOfTheMonth = (date: Date): Date => {
  return new Date(date.getFullYear(), date.getMonth(), 1, date.getHours());
};

const getLastDayOfThePreviousMonth = (date: Date): Date => {
  return new Date(date.getFullYear(), date.getMonth(), 0, date.getHours());
};

const getMondayOfTheFirstWeekOfMonth = (date: Date): Date => {
  const firstDayOfTheMonth = getFirstDayOfTheMonth(date);
  const firstMondayOfTheMonth = getFirstDayOfTheWeek(firstDayOfTheMonth);

  return firstMondayOfTheMonth;
};

const getSundayOfTheLastCompleteWeekOfPreviousMonth = (date: Date): Date => {
  const lastDayOfPreviousMonth = getLastDayOfThePreviousMonth(date);
  const lastSundayOfPreviousMonth = getLastDayOfTheWeek(lastDayOfPreviousMonth);
  const lastDayOfThePreviousWeek = getLastDayOfThePreviousWeek(lastSundayOfPreviousMonth);

  if (date >= lastDayOfPreviousMonth) {
    return lastSundayOfPreviousMonth;
  }
  return lastDayOfThePreviousWeek;
};

const getStartDate: (filters: TimeToEnrichFilters) => string = filters => {
  let date = new Date();

  if (filters.period === PredefinedPeriod.LAST_MONTH) {
    date.setMonth(date.getMonth() - 1);
    return getMondayOfTheFirstWeekOfMonth(date).toISOString().substr(0, 10);
  }

  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    date.setFullYear(date.getFullYear() - 1);
    return getFirstDayOfTheMonth(date).toISOString().substr(0, 10);
  }

  date.setDate(date.getDate() - 12 * 7);
  return getFirstDayOfTheWeek(date).toISOString().substr(0, 10);
};

const getEndDate: (filters: TimeToEnrichFilters) => string = filters => {
  let date = new Date();

  if (filters.period === PredefinedPeriod.LAST_MONTH) {
    return getSundayOfTheLastCompleteWeekOfPreviousMonth(date).toISOString().substr(0, 10);
  }

  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    return getLastDayOfThePreviousMonth(date).toISOString().substr(0, 10);
  }

  return getLastDayOfThePreviousWeek(date).toISOString().substr(0, 10);
};

const getPeriodType: (filters: TimeToEnrichFilters) => string = filters => {
  if (filters.period === PredefinedPeriod.LAST_12_MONTHS) {
    return 'month';
  }

  return 'week';
};

export {
  getStartDate,
  getEndDate,
  getPeriodType,
  getFirstDayOfTheWeek,
  getLastDayOfThePreviousWeek,
  getLastDayOfThePreviousMonth,
  getFirstDayOfTheMonth,
  getMondayOfTheFirstWeekOfMonth,
  getSundayOfTheLastCompleteWeekOfPreviousMonth,
};
