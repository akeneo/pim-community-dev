const availableFrequencyOptions = ['daily', 'weekly', 'every_4_hours', 'every_8_hours', 'every_12_hours'];
const weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

type FrequencyOption = typeof availableFrequencyOptions[number];
type CronExpression = string;

const isHourlyFrequency = (frequencyOption: FrequencyOption): boolean =>
  ['every_4_hours', 'every_8_hours', 'every_12_hours'].includes(frequencyOption);

const getFrequencyOptionFromCronExpression = (cronExpression: CronExpression): FrequencyOption => {
  const [, hours, , , weekDayNumber] = cronExpression.split(' ');

  if ('*' !== weekDayNumber) {
    return 'weekly';
  }

  if (hours.endsWith('/4')) {
    return 'every_4_hours';
  }

  if (hours.endsWith('/8')) {
    return 'every_8_hours';
  }

  if (hours.endsWith('/12')) {
    return 'every_12_hours';
  }

  return 'daily';
};

const getWeekDayFromCronExpression = (cronExpression: CronExpression): string => {
  const [, , , , weekDay] = cronExpression.split(' ');

  const weekDayNumber = isNaN(Number(weekDay)) ? 0 : Number(weekDay);

  return weekDays[weekDayNumber];
};

const getMinutesFromCronExpression = (cronExpression: CronExpression): string => {
  const [minutes] = cronExpression.split(' ');

  return minutes.padStart(2, '0');
};

const getHoursFromCronExpression = (cronExpression: CronExpression): string => {
  const [, hours] = cronExpression.split(' ');

  return hours.split('/')[0].padStart(2, '0');
};

const getCronExpressionFromFrequencyOption = (
  frequencyOption: FrequencyOption,
  cronExpression: CronExpression
): CronExpression => {
  const [minutes] = cronExpression.split(' ');
  const hours = getHoursFromCronExpression(cronExpression);

  switch (frequencyOption) {
    case 'daily':
      return `${Number(minutes)} ${Number(hours)} * * *`;
    case 'weekly':
      return `${Number(minutes)} ${Number(hours)} * * 0`;
    case 'every_4_hours':
      return '0 0/4 * * *';
    case 'every_8_hours':
      return '0 0/8 * * *';
    case 'every_12_hours':
      return '0 0/12 * * *';
    default:
      throw new Error(`Unsupported frequency option: "${frequencyOption}"`);
  }
};

const getWeeklyCronExpressionFromWeekDay = (weekDay: string, cronExpression: CronExpression): CronExpression => {
  const [minutes, hours] = cronExpression.split(' ');

  return `${minutes} ${hours} * * ${weekDays.indexOf(weekDay)}`;
};

const getCronExpressionFromHours = (hours: string, cronExpression: CronExpression): CronExpression => {
  const [minutes, , , , weekDay] = cronExpression.split(' ');

  return `${Number(minutes)} ${Number(hours)} * * ${weekDay}`;
};

const getCronExpressionFromMinutes = (minutes: string, cronExpression: CronExpression): CronExpression => {
  const [, hours, , , weekDay] = cronExpression.split(' ');

  return `${Number(minutes)} ${Number(hours)} * * ${weekDay}`;
};

export type {FrequencyOption, CronExpression};
export {
  availableFrequencyOptions,
  getCronExpressionFromFrequencyOption,
  getCronExpressionFromHours,
  getCronExpressionFromMinutes,
  getFrequencyOptionFromCronExpression,
  getHoursFromCronExpression,
  getMinutesFromCronExpression,
  getWeekDayFromCronExpression,
  getWeeklyCronExpressionFromWeekDay,
  isHourlyFrequency,
  weekDays,
};
