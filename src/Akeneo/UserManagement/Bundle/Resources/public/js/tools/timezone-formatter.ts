const formatTimezoneOffsetFromUTC = (timeZone: string): string => {
  const now = new Date();

  const dateWithTimeZone = now.toLocaleString('en-US', {timeZone});
  const dateWithLocaleTimeZone = now.toLocaleString('en-US');
  const diff = (Date.parse(dateWithLocaleTimeZone) - Date.parse(dateWithTimeZone)) / 3600000;
  const hourOffset = -(diff + now.getTimezoneOffset() / 60);

  return hourOffset >= 0 ? '+' + formatTimezoneOffset(hourOffset) : '-' + formatTimezoneOffset(hourOffset);
};

const formatTimezoneOffset = (hourOffset: number): string => {
  const absHourOffset = Math.abs(hourOffset);
  const hours = Math.floor(absHourOffset);
  const minutes = (absHourOffset - hours) * 60;

  return hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
};

export {formatTimezoneOffsetFromUTC};
