import {useCallback, useContext} from 'react';
import {UserContext} from '../user';

export const useNumberFormatter = () => {
    const user = useContext(UserContext);

    const uiLocale = user.get('uiLocale');

    return useCallback(
        (number: number, options?: Intl.NumberFormatOptions) =>
            new Intl.NumberFormat(uiLocale.replace('_', '-'), options).format(number),
        [uiLocale]
    );
};
