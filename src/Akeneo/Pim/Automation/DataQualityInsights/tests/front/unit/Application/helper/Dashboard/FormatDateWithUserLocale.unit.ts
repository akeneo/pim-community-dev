import {
  dailyCallback, monthlyCallback, weeklyCallback
} from "@akeneo-pim-ee/data-quality-insights/src/application/helper/Dashboard/FormatDateWithUserLocale";

const UserContext = require('pim/user-context');
UserContext.get.mockImplementation(() => 'en_US');

beforeEach(() =>  {
  jest.resetModules();
});

describe('Convert dashboard dates depending on the user locale and time period', () => {
  test('convert a date for daily time period', () => {
    expect(dailyCallback('2020-02-06')).toBe('Thursday, February 6');
  });
  test('convert last day of week to a date range for weekly time period', () => {
    expect(weeklyCallback('2020-02-02')).toBe('1/27/2020 - 2/2/2020');
  });
  test('convert last day of week to a date range for weekly time period, across 2 years', () => {
    expect(weeklyCallback('2020-01-05')).toBe('12/30/2019 - 1/5/2020');
  });
  test('convert a date for monthly time period', () => {
    expect(monthlyCallback('2020-01-31')).toBe('January 2020');
    expect(monthlyCallback('2020-01-15')).toBe('January 2020');
  });
});
