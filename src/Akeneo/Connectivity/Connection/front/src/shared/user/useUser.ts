import {useContext} from 'react';
import {UserContext} from './user-context';

export const useUser = (): {locale: string; timeZone: string} => {
    const user = useContext(UserContext);

    const locale = user.get<string>('uiLocale').replace('_', '-');
    const timeZone = user.get<string>('timezone');

    return {locale, timeZone};
};
