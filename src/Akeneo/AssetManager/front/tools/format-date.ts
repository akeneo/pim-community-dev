const formatDateForUILocale = (
  date: string,
  uiLocale: string,
  timeZone: string,
  options?: Intl.DateTimeFormatOptions
): string => {
  const locale = uiLocale.replace('_', '-');
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
