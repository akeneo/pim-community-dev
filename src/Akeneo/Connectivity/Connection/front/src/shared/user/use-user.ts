import {useContext} from 'react';
import {UserContext} from './user-context';

export const useUser = () => {
    const user = useContext(UserContext);

    const locale = user.get('uiLocale').replace('_', '-');
    const timeZone = user.get('timezone');

    return {locale, timeZone};
};
