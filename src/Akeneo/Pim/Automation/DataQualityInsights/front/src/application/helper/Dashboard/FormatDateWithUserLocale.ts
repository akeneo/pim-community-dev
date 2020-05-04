const UserContext = require('pim/user-context');
const __ = require('oro/translator');

export const weeklyCallback = (lastDayOfWeek: string) => {
  const uiLocale = UserContext.get('uiLocale');

  const endDate = new Date(lastDayOfWeek);
  const startDate = new Date(lastDayOfWeek);
  startDate.setDate(startDate.getDate() - 6);

  return new Intl.DateTimeFormat(uiLocale.replace('_', '-')).format(startDate) + ' - ' + new Intl.DateTimeFormat(uiLocale.replace('_', '-')).format(endDate);
};

export const dailyCallback = (_: string, index: number) => {
  return `${__('akeneo_data_quality_insights.dqi_dashboard.time_axis.day').charAt(0)} - ${7 - index}`;
};

export const monthlyCallback = (date: string) => {
  const uiLocale = UserContext.get('uiLocale');

  return new Intl.DateTimeFormat(
    uiLocale.replace('_', '-'),
    {month: "long", year: "numeric"}
  ).format(new Date(date));
};
