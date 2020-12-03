const UserContext = require('pim/user-context');
const __ = require('oro/translator');

export const weeklyCallback = (lastDayOfWeek: string) => {
  const uiLocale = UserContext.get('uiLocale');

  const endDate = new Date(lastDayOfWeek);
  const startDate = new Date(lastDayOfWeek);
  startDate.setDate(startDate.getDate() - 6);

  const formattedStartDate = new Intl.DateTimeFormat(uiLocale.replace('_', '-'), {day: 'numeric', month: 'short'})
    .format(startDate)
    .replace(/\s/g, '. ');

  const formattedEndDate = new Intl.DateTimeFormat(uiLocale.replace('_', '-'), {day: 'numeric', month: 'short'})
    .format(endDate)
    .replace(/\s/g, '. ');

  return `${formattedStartDate} - ${formattedEndDate}`;
};

export const dailyCallback = (_: string, index: number) => {
  return `${__('akeneo_data_quality_insights.dqi_dashboard.time_axis.day').charAt(0)} - ${7 - index}`;
};

export const monthlyCallback = (date: string) => {
  const uiLocale = UserContext.get('uiLocale');

  return new Intl.DateTimeFormat(uiLocale.replace('_', '-'), {month: 'short', year: '2-digit'})
    .format(new Date(date))
    .replace(/\s/g, '. ');
};
