import {getPeriodType, getStartDate} from './TimeToEnrichFilters';
import {Aggregation, Metric, PredefinedComparison, PredefinedPeriod} from '../../Common';

describe('TimeToEnrichFilters', () => {
  it('should return the date 12 weeks ago starting with the beginning of the week', async () => {
    let date = new Date();
    date.setDate(date.getDate() - 12 * 7);
    const day = date.getDay();
    const diff = date.getDate() - day + (day === 0 ? -6 : 1);
    const startDate = new Date(date.setDate(diff));

    expect(
      getStartDate({
        metric: Metric.TIME_TO_ENRICH,
        period: PredefinedPeriod.LAST_12_WEEKS,
        aggregation: Aggregation.FAMILIES,
        comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
        families: [],
        channels: [],
        locales: [],
      })
    ).toBe(startDate.toISOString().substr(0, 10));
  });

  it('should return the date 1 year ago starting with the beginning of the month', async () => {
    let date = new Date();
    date.setFullYear(date.getFullYear() - 1);
    const startDate = new Date(date.getFullYear(), date.getMonth(), 1, date.getHours());

    expect(
      getStartDate({
        metric: Metric.TIME_TO_ENRICH,
        period: PredefinedPeriod.LAST_12_MONTHS,
        aggregation: Aggregation.FAMILIES,
        comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
        families: [],
        channels: [],
        locales: [],
      })
    ).toBe(startDate.toISOString().substr(0, 10));
  });

  it('should return a week value', async () => {
    expect(
      getPeriodType({
        metric: Metric.TIME_TO_ENRICH,
        period: PredefinedPeriod.LAST_12_WEEKS,
        aggregation: Aggregation.FAMILIES,
        comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
        families: [],
        channels: [],
        locales: [],
      })
    ).toBe('week');
  });

  it('should return a month value', async () => {
    expect(
      getPeriodType({
        metric: Metric.TIME_TO_ENRICH,
        period: PredefinedPeriod.LAST_12_MONTHS,
        aggregation: Aggregation.FAMILIES,
        comparison: PredefinedComparison.SAME_PERIOD_LAST_YEAR,
        families: [],
        channels: [],
        locales: [],
      })
    ).toBe('month');
  });
});
