import {createContext} from 'react';
import {User} from './user.interface';

export const UserContext = createContext<User>({
    get: (data: string) => data,
    set: () => undefined,
});
