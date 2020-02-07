const UserContext = require('pim/user-context');

export const weeklyCallback = (lastDayOfWeek: string) => {
  const uiLocale = UserContext.get('uiLocale');

  const endDate = new Date(lastDayOfWeek);
  const startDate = new Date(lastDayOfWeek);
  startDate.setDate(startDate.getDate() - 6);

  return new Intl.DateTimeFormat(uiLocale.replace('_', '-')).format(startDate) + ' - ' + new Intl.DateTimeFormat(uiLocale.replace('_', '-')).format(endDate);
};

export const dailyCallback = (date: string) => {
  const uiLocale = UserContext.get('uiLocale');

  return new Intl.DateTimeFormat(
    uiLocale.replace('_', '-'),
    {weekday: "long", month: "long", day: "numeric"}
  ).format(new Date(date));
};

export const monthlyCallback = (date: string) => {
  const uiLocale = UserContext.get('uiLocale');

  return new Intl.DateTimeFormat(
    uiLocale.replace('_', '-'),
    {month: "long", year: "numeric"}
  ).format(new Date(date));
};
