import {useCallback, useContext} from 'react';
import {UserContext} from '../user';

export const useDateFormatter = () => {
    const user = useContext(UserContext);

    const uiLocale = user.get('uiLocale');
    const timezone = user.get('timezone');

    return useCallback(
        (date: string, options?: Intl.DateTimeFormatOptions) => {
            if (undefined === options || undefined === options.timeZone) {
                options = {...options, timeZone: timezone};
            }

            return new Intl.DateTimeFormat(uiLocale.replace('_', '-'), options).format(new Date(date));
        },
        [uiLocale]
    );
};
