import {
  getCronExpressionFromFrequencyOption,
  getFrequencyOptionFromCronExpression,
  getTimeInUserTimezone,
  getWeekDayFromCronExpression,
  getWeeklyCronExpressionFromWeekDay,
} from './Frequency';

test('it can get a frequency option from a cron expression', () => {
  expect(getFrequencyOptionFromCronExpression('0 0 * * *')).toBe('daily');
  expect(getFrequencyOptionFromCronExpression('0 0 * * 0')).toBe('weekly');
  expect(getFrequencyOptionFromCronExpression('0 */4 * * *')).toBe('every_4_hours');
  expect(getFrequencyOptionFromCronExpression('0 */8 * * *')).toBe('every_8_hours');
  expect(getFrequencyOptionFromCronExpression('0 */12 * * *')).toBe('every_12_hours');
});

test('it can get a weekday from a cron expression', () => {
  expect(getWeekDayFromCronExpression('0 0 * * 0')).toBe('sunday');
  expect(getWeekDayFromCronExpression('0 0 * * 1')).toBe('monday');
  expect(getWeekDayFromCronExpression('0 0 * * 2')).toBe('tuesday');
  expect(getWeekDayFromCronExpression('0 0 * * 3')).toBe('wednesday');
  expect(getWeekDayFromCronExpression('0 0 * * 4')).toBe('thursday');
  expect(getWeekDayFromCronExpression('0 0 * * 5')).toBe('friday');
  expect(getWeekDayFromCronExpression('0 0 * * 6')).toBe('saturday');
  expect(getWeekDayFromCronExpression('0 0 * * *')).toBe('sunday');
});

test('it can get a cron expression from a given frequency option and existing expression', () => {
  expect(getCronExpressionFromFrequencyOption('daily', '0 0 * * 5')).toBe('0 0 * * *');
  expect(getCronExpressionFromFrequencyOption('daily', '0 */8 * * *')).toBe('0 0 * * *');

  expect(getCronExpressionFromFrequencyOption('weekly', '0 0 * * 5')).toBe('0 0 * * 0');
  expect(getCronExpressionFromFrequencyOption('weekly', '0 */4 * * *')).toBe('0 0 * * 0');

  expect(getCronExpressionFromFrequencyOption('every_4_hours', '0 0 * * 5')).toBe('0 */4 * * *');
  expect(getCronExpressionFromFrequencyOption('every_4_hours', '43 9 * * 5')).toBe('0 */4 * * *');

  expect(getCronExpressionFromFrequencyOption('every_8_hours', '0 0 * * 5')).toBe('0 */8 * * *');
  expect(getCronExpressionFromFrequencyOption('every_8_hours', '43 9 * * *')).toBe('0 */8 * * *');

  expect(getCronExpressionFromFrequencyOption('every_12_hours', '0 0 * * 5')).toBe('0 */12 * * *');
  expect(getCronExpressionFromFrequencyOption('every_12_hours', '43 9 * * *')).toBe('0 */12 * * *');

  expect(() => getCronExpressionFromFrequencyOption('unknown', '0 0 * * 7')).toThrowError(
    'Unsupported frequency option: "unknown"'
  );
});

test('it can get a weekly cron expression from a given week day number and an existing expression', () => {
  expect(getWeeklyCronExpressionFromWeekDay('monday', '0 0 * * *')).toBe('0 0 * * 1');
  expect(getWeeklyCronExpressionFromWeekDay('wednesday', '0 0 * * *')).toBe('0 0 * * 3');
  expect(getWeeklyCronExpressionFromWeekDay('saturday', '4 8 * * *')).toBe('4 8 * * 6');
  expect(getWeeklyCronExpressionFromWeekDay('sunday', '41 8 * * *')).toBe('41 8 * * 0');
});
