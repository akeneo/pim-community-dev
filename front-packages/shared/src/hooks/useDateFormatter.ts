import {useCallback} from 'react';
import {useUserContext} from './useUserContext';

const useDateFormatter = () => {
  const user = useUserContext();
  const locale = user.get('uiLocale')?.replace('_', '-') ?? 'en-US';
  const timeZone = user.get('timezone');

  return useCallback(
    (date: string | number, options?: Intl.DateTimeFormatOptions) => {
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
    },
    [locale, timeZone]
  );
};

export {useDateFormatter};
