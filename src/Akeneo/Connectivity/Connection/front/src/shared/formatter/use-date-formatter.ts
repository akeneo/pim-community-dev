import {useCallback, useContext} from 'react';
import {UserContext} from '../user';

export const useDateFormatter = () => {
    const user = useContext(UserContext);

    const uiLocale = user.get('uiLocale');

    return useCallback(
        (date: string, options?: Intl.DateTimeFormatOptions) =>
            new Intl.DateTimeFormat(uiLocale.replace('_', '-'), options).format(new Date(date)),
        [uiLocale]
    );
};
