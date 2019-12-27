import {useContext} from 'react';
import {UserContext} from '../user';

export const useDateFormatter = () => {
    const user = useContext(UserContext);

    return (date: string, options?: Intl.DateTimeFormatOptions) => {
        return new Intl.DateTimeFormat(user.get('catalogLocale').replace('_', '-'), options).format(new Date(date));
    };
};
