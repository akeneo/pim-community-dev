const availableFrequencyOptions = ['daily', 'weekly', 'every_4_hours', 'every_8_hours', 'every_12_hours'];
const weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

type FrequencyOption = typeof availableFrequencyOptions[number];
type CronExpression = string;

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

const getHoursFromCronExpression = (cronExpression: CronExpression): string => {
  const [, hours] = cronExpression.split(' ');

  return hours.includes('/') ? hours.split('/')[0] : hours;
};

const getTimeFromCronExpression = (cronExpression: CronExpression): string => {
  const [minutes] = cronExpression.split(' ');

  return `${getHoursFromCronExpression(cronExpression).padStart(2, '0')}:${minutes.padStart(2, '0')}`;
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
      return `${minutes} ${hours}/4 * * *`;
    case 'every_8_hours':
      return `${minutes} ${hours}/8 * * *`;
    case 'every_12_hours':
      return `${minutes} ${hours}/12 * * *`;
    default:
      throw new Error(`Unsupported frequency option: ${frequencyOption}`);
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

const getHourlyCronExpressionFromTime = (time: string, cronExpression: CronExpression): CronExpression => {
  const [hours, minutes] = time.split(':');
  const [, hourlyFrequency] = cronExpression.split(' ');
  const [, frequency] = hourlyFrequency.split('/');

  return `${Number(minutes)} ${Number(hours)}/${frequency} * * *`;
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
