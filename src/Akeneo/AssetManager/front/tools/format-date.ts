const UserContext = require('pim/user-context');

const formatDateForUILocale = (date: string, options?: Intl.DateTimeFormatOptions): string => {
  const locale = UserContext.get('uiLocale').replace('_', '-');
  const timeZone = UserContext.get('timezone');
  options = {timeZone, ...options};

  try {
    return new Intl.DateTimeFormat(locale, options).format(new Date(date));
  } catch (error) {
    if (error instanceof RangeError) {
      return new Intl.DateTimeFormat(locale, {...options, timeZone: 'UTC', timeZoneName: 'short'}).format(
        new Date(date)
      );
    }

    throw error;
  }
};

export {formatDateForUILocale};
