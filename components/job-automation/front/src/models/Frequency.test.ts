import {
  getCronExpressionFromFrequencyOption,
  getDailyCronExpressionFromTime,
  getFrequencyOptionFromCronExpression,
  getHourlyCronExpressionFromTime,
  getTimeFromCronExpression,
  getWeekDayFromCronExpression,
  getWeeklyCronExpressionFromTime,
  getWeeklyCronExpressionFromWeekDay,
} from './Frequency';

test('it can get a frequency option from a cron expression', () => {
  expect(getFrequencyOptionFromCronExpression('0 0 * * *')).toBe('daily');
  expect(getFrequencyOptionFromCronExpression('0 0 * * 0')).toBe('weekly');
  expect(getFrequencyOptionFromCronExpression('0 0 * * 1')).toBe('weekly');
  expect(getFrequencyOptionFromCronExpression('0 0,4,8,12,16,20 * * *')).toBe('every_4_hours');
  expect(getFrequencyOptionFromCronExpression('0 1,9,17 * * *')).toBe('every_8_hours');
  expect(getFrequencyOptionFromCronExpression('0 2,14 * * *')).toBe('every_12_hours');

  expect(() => getFrequencyOptionFromCronExpression('0 0,1,2,3,4,5,6,7 * * *')).toThrowError(
    'Unsupported cron expression: "0 0,1,2,3,4,5,6,7 * * *"'
  );
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

test('it can get time from a cron expression', () => {
  expect(getTimeFromCronExpression('0 0 * * *')).toBe('00:00');
  expect(getTimeFromCronExpression('45 5 * * *')).toBe('05:45');
  expect(getTimeFromCronExpression('5 7 * * *')).toBe('07:05');
  expect(getTimeFromCronExpression('6 11 * * *')).toBe('11:06');
  expect(getTimeFromCronExpression('36 10 * * *')).toBe('10:36');
  expect(getTimeFromCronExpression('36 2,6,10,14,18,22 * * *')).toBe('02:36');
  expect(getTimeFromCronExpression('36 11,23 * * *')).toBe('11:36');
  expect(getTimeFromCronExpression('36 3,11,19 * * *')).toBe('03:36');
});

test('it can get a cron expression from a given frequency option and existing expression', () => {
  expect(getCronExpressionFromFrequencyOption('daily', '0 0 * * 5')).toBe('0 0 * * *');
  expect(getCronExpressionFromFrequencyOption('daily', '5 3,15 * * 5')).toBe('5 3 * * *');
  expect(getCronExpressionFromFrequencyOption('daily', '5 2,6,10,14,18,22 * * 5')).toBe('5 2 * * *');
  expect(getCronExpressionFromFrequencyOption('daily', '58 0,12 * * 5')).toBe('58 0 * * *');

  expect(getCronExpressionFromFrequencyOption('weekly', '0 0 * * 5')).toBe('0 0 * * 0');
  expect(getCronExpressionFromFrequencyOption('weekly', '2 3,11,19 * * *')).toBe('2 3 * * 0');

  expect(getCronExpressionFromFrequencyOption('every_4_hours', '0 0 * * 5')).toBe('0 0,4,8,12,16,20 * * *');
  expect(getCronExpressionFromFrequencyOption('every_4_hours', '43 9 * * 5')).toBe('43 9,13,17,21,1,5 * * *');
  expect(getCronExpressionFromFrequencyOption('every_4_hours', '43 1 * * 5')).toBe('43 1,5,9,13,17,21 * * *');

  expect(getCronExpressionFromFrequencyOption('every_8_hours', '0 0 * * 5')).toBe('0 0,8,16 * * *');
  expect(getCronExpressionFromFrequencyOption('every_8_hours', '43 9 * * 5')).toBe('43 9,17,1 * * *');
  expect(getCronExpressionFromFrequencyOption('every_8_hours', '43 0,4,8,12,16,20 * * 5')).toBe('43 0,8,16 * * *');

  expect(getCronExpressionFromFrequencyOption('every_12_hours', '0 0 * * 5')).toBe('0 0,12 * * *');
  expect(getCronExpressionFromFrequencyOption('every_12_hours', '43 9 * * 5')).toBe('43 9,21 * * *');
  expect(getCronExpressionFromFrequencyOption('every_12_hours', '43 9,21 * * 5')).toBe('43 9,21 * * *');

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

test('it can get a weekly cron expression from a given time and an existing expression', () => {
  expect(getWeeklyCronExpressionFromTime('00:00', '0 0 * * *')).toBe('0 0 * * *');
  expect(getWeeklyCronExpressionFromTime('05:45', '0 0 * * 1')).toBe('45 5 * * 1');
  expect(getWeeklyCronExpressionFromTime('07:05', '0 0 * * 2')).toBe('5 7 * * 2');
  expect(getWeeklyCronExpressionFromTime('11:06', '0 0 * * 3')).toBe('6 11 * * 3');
  expect(getWeeklyCronExpressionFromTime('10:36', '0 0 * * 4')).toBe('36 10 * * 4');
});

test('it can get a daily cron expression from a given time', () => {
  expect(getDailyCronExpressionFromTime('00:00')).toBe('0 0 * * *');
  expect(getDailyCronExpressionFromTime('05:45')).toBe('45 5 * * *');
  expect(getDailyCronExpressionFromTime('07:05')).toBe('5 7 * * *');
  expect(getDailyCronExpressionFromTime('11:06')).toBe('6 11 * * *');
  expect(getDailyCronExpressionFromTime('10:36')).toBe('36 10 * * *');
});

test('it can get a hourly cron expression from a given time and a frequency option', () => {
  expect(getHourlyCronExpressionFromTime('05:45', 'every_4_hours')).toBe('45 5,9,13,17,21,1 * * *');
  expect(getHourlyCronExpressionFromTime('00:45', 'every_4_hours')).toBe('45 0,4,8,12,16,20 * * *');
  expect(getHourlyCronExpressionFromTime('07:05', 'every_8_hours')).toBe('5 7,15,23 * * *');
  expect(getHourlyCronExpressionFromTime('11:06', 'every_12_hours')).toBe('6 11,23 * * *');
  expect(getHourlyCronExpressionFromTime('12:06', 'every_12_hours')).toBe('6 12,0 * * *');

  expect(() => getHourlyCronExpressionFromTime('00:00', 'daily')).toThrowError(
    'Unsupported hourly frequency option: "daily"'
  );
});
