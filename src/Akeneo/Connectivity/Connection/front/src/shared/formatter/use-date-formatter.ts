import {useCallback, useContext} from 'react';
import {UserContext} from '../user';

export const useDateFormatter = () => {
    const user = useContext(UserContext);

    const locale = user.get('uiLocale').replace('_', '-');
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
