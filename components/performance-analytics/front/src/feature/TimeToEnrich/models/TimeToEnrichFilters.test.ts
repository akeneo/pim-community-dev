import {
  getPeriodType,
  getLastDayOfThePreviousMonth,
  getFirstDayOfTheWeek,
  getLastDayOfThePreviousWeek,
  getFirstDayOfTheMonth,
  getMondayOfTheFirstWeekOfMonth,
  getSundayOfTheLastCompleteWeekOfPreviousMonth,
} from './TimeToEnrichFilters';
import {Aggregation, Metric, PredefinedComparison, PredefinedPeriod} from '../../Common';

describe('TimeToEnrichFilters', () => {
  it('should return the first day of the current week', async () => {
    expect(getFirstDayOfTheWeek(new Date('2022-12-01T00:00:00'))).toStrictEqual(new Date('2022-11-28T00:00:00'));
    expect(getFirstDayOfTheWeek(new Date('2022-11-14T00:00:00'))).toStrictEqual(new Date('2022-11-14T00:00:00'));
    expect(getFirstDayOfTheWeek(new Date('2022-11-06T00:00:00'))).toStrictEqual(new Date('2022-10-31T00:00:00'));
  });

  it('should return the last day of the previous week', async () => {
    expect(getLastDayOfThePreviousWeek(new Date('2022-12-01T00:00:00'))).toStrictEqual(new Date('2022-11-27T00:00:00'));
    expect(getLastDayOfThePreviousWeek(new Date('2022-12-04T00:00:00'))).toStrictEqual(new Date('2022-11-27T00:00:00'));
    expect(getLastDayOfThePreviousWeek(new Date('2022-10-31T00:00:00'))).toStrictEqual(new Date('2022-10-30T00:00:00'));
  });

  it('should return the first day of the month', async () => {
    expect(getFirstDayOfTheMonth(new Date('2022-12-01T00:00:00'))).toStrictEqual(new Date('2022-12-01T00:00:00'));
    expect(getFirstDayOfTheMonth(new Date('2022-11-30T00:00:00'))).toStrictEqual(new Date('2022-11-01T00:00:00'));
    expect(getFirstDayOfTheMonth(new Date('2022-10-31T00:00:00'))).toStrictEqual(new Date('2022-10-01T00:00:00'));
  });

  it('should return the last day of the previous month', async () => {
    expect(getLastDayOfThePreviousMonth(new Date('2022-12-07T00:00:00'))).toStrictEqual(
      new Date('2022-11-30T00:00:00')
    );
    expect(getLastDayOfThePreviousMonth(new Date('2022-12-01T00:00:00'))).toStrictEqual(
      new Date('2022-11-30T00:00:00')
    );
    expect(getLastDayOfThePreviousMonth(new Date('2022-11-30T00:00:00'))).toStrictEqual(
      new Date('2022-10-31T00:00:00')
    );
    expect(getLastDayOfThePreviousMonth(new Date('2022-10-31T00:00:00'))).toStrictEqual(
      new Date('2022-09-30T00:00:00')
    );
  });

  it('should return the monday of the first week of the month', async () => {
    expect(getMondayOfTheFirstWeekOfMonth(new Date('2022-12-01T00:00:00'))).toStrictEqual(
      new Date('2022-11-28T00:00:00')
    );
    expect(getMondayOfTheFirstWeekOfMonth(new Date('2022-11-06T00:00:00'))).toStrictEqual(
      new Date('2022-10-31T00:00:00')
    );
    expect(getMondayOfTheFirstWeekOfMonth(new Date('2022-10-26T00:00:00'))).toStrictEqual(
      new Date('2022-09-26T00:00:00')
    );
  });

  it('should return the sunday of the last week of the previous month', async () => {
    expect(getSundayOfTheLastCompleteWeekOfPreviousMonth(new Date('2022-12-12T00:00:00'))).toStrictEqual(
      new Date('2022-12-04T00:00:00')
    );
    expect(getSundayOfTheLastCompleteWeekOfPreviousMonth(new Date('2022-12-01T00:00:00'))).toStrictEqual(
      new Date('2022-11-27T00:00:00')
    );
    expect(getSundayOfTheLastCompleteWeekOfPreviousMonth(new Date('2022-11-06T00:00:00'))).toStrictEqual(
      new Date('2022-11-06T00:00:00')
    );
    expect(getSundayOfTheLastCompleteWeekOfPreviousMonth(new Date('2022-10-31T00:00:00'))).toStrictEqual(
      new Date('2022-10-02T00:00:00')
    );
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
