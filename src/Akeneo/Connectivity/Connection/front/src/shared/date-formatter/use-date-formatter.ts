import {useCallback, useContext} from 'react';
import {UserContext} from '../user';

export const useDateFormatter = () => {
    const user = useContext(UserContext);

    const catalogLocale = user.get('catalogLocale');

    return useCallback(
        (date: string, options?: Intl.DateTimeFormatOptions) =>
            new Intl.DateTimeFormat(catalogLocale.replace('_', '-'), options).format(new Date(date)),
        [catalogLocale]
    );
};
