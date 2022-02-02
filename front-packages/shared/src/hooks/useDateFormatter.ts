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
        /**
         * The dateStyle option comes from ES2020 DateTimeFormatOptions
         * The timeZoneName option comes from ES5 DateTimeFormatOptions
         * These 2 options are incompatible.
         */
        if (options.dateStyle && options.timeZoneName) {
          delete options.timeZoneName;
        }

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
