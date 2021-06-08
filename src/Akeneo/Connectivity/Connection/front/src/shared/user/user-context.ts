import {createContext} from 'react';
import {User} from './user.interface';

export const UserContext = createContext<User>({
    get: <T>(data: string) => data as unknown as T,
    set: () => undefined,
    refresh: () => Promise.resolve(),
});
