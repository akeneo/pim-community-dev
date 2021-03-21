import {useCallback} from 'react';
import {useUser} from '../user';

export const useDateFormatter = (): ((date: Date, options: Intl.DateTimeFormatOptions) => string) => {
    const {locale, timeZone} = useUser();

    return useCallback(
        (date: Date, options?: Intl.DateTimeFormatOptions) => {
            options = {timeZone, ...options};

            try {
                return new Intl.DateTimeFormat(locale, options).format(date);
            } catch (error) {
                if (error instanceof RangeError) {
                    return new Intl.DateTimeFormat(locale, {...options, timeZone: 'UTC', timeZoneName: 'short'}).format(
                        date
                    );
                }

                throw error;
            }
        },
        [locale, timeZone]
    );
};
