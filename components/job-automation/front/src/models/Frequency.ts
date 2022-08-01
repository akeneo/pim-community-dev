const availableFrequencyOptions = ['daily', 'weekly', 'every_4_hours', 'every_8_hours', 'every_12_hours'];
const weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

type FrequencyOption = typeof availableFrequencyOptions[number];
type CronExpression = string;

const getFrequencyOptionFromCronExpression = (cronExpression: CronExpression): FrequencyOption => {
  const [, hours, , , weekDayNumber] = cronExpression.split(' ');

  if ('*' !== weekDayNumber) {
    return 'weekly';
  }

  switch (hours.split(',').length) {
    case 1:
      return 'daily';
    case 2:
      return 'every_12_hours';
    case 3:
      return 'every_8_hours';
    case 6:
      return 'every_4_hours';
    default:
      throw new Error(`Unsupported cron expression: "${cronExpression}"`);
  }
};

const getWeekDayFromCronExpression = (cronExpression: CronExpression): string => {
  const [, , , , weekDay] = cronExpression.split(' ');

  const weekDayNumber = isNaN(Number(weekDay)) ? 0 : Number(weekDay);

  return weekDays[weekDayNumber];
};

const getHoursFromCronExpression = (cronExpression: CronExpression): string => {
  const [, hours] = cronExpression.split(' ');

  return hours.split(',')[0];
};

const getTimeFromCronExpression = (cronExpression: CronExpression): string => {
  const [minutes] = cronExpression.split(' ');
  const hours = getHoursFromCronExpression(cronExpression);

  return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
};

const getCronExpressionFromFrequencyOption = (
  frequencyOption: FrequencyOption,
  cronExpression: CronExpression
): CronExpression => {
  const [minutes] = cronExpression.split(' ');
  const hours = getHoursFromCronExpression(cronExpression);

  switch (frequencyOption) {
    case 'daily':
      return `${minutes} ${hours} * * *`;
    case 'weekly':
      return `${minutes} ${hours} * * 0`;
    case 'every_4_hours':
    case 'every_8_hours':
    case 'every_12_hours':
      return `${minutes} ${getHourlyStepsFromFrequencyOption(frequencyOption, Number(hours))} * * *`;
    default:
      throw new Error(`Unsupported frequency option: "${frequencyOption}"`);
  }
};

const getWeeklyCronExpressionFromWeekDay = (weekDay: string, cronExpression: CronExpression): CronExpression => {
  const [minutes, hours] = cronExpression.split(' ');

  return `${minutes} ${hours} * * ${weekDays.indexOf(weekDay)}`;
};

const getWeeklyCronExpressionFromTime = (time: string, cronExpression: CronExpression): CronExpression => {
  const [hours, minutes] = time.split(':');
  const [, , , , weekDay] = cronExpression.split(' ');

  return `${Number(minutes)} ${Number(hours)} * * ${weekDay}`;
};

const getDailyCronExpressionFromTime = (time: string): CronExpression => {
  const [hours, minutes] = time.split(':');

  return `${Number(minutes)} ${Number(hours)} * * *`;
};

const getHourlyStepsFromFrequencyOption = (frequencyOption: FrequencyOption, hour: number): number[] => {
  switch (frequencyOption) {
    case 'every_4_hours':
      return [0, 4, 8, 12, 16, 20].map(step => (hour + step) % 24);
    case 'every_8_hours':
      return [0, 8, 16].map(step => (hour + step) % 24);
    case 'every_12_hours':
      return [0, 12].map(step => (hour + step) % 24);
    default:
      throw new Error(`Unsupported hourly frequency option: "${frequencyOption}"`);
  }
};

const getHourlyCronExpressionFromTime = (time: string, frequencyOption: FrequencyOption): CronExpression => {
  const [hours, minutes] = time.split(':');
  const hourlySteps = getHourlyStepsFromFrequencyOption(frequencyOption, Number(hours));

  return `${Number(minutes)} ${hourlySteps.join(',')} * * *`;
};

export type {FrequencyOption, CronExpression};
export {
  availableFrequencyOptions,
  getCronExpressionFromFrequencyOption,
  getDailyCronExpressionFromTime,
  getFrequencyOptionFromCronExpression,
  getHourlyCronExpressionFromTime,
  getTimeFromCronExpression,
  getWeekDayFromCronExpression,
  getWeeklyCronExpressionFromTime,
  getWeeklyCronExpressionFromWeekDay,
  weekDays,
};
