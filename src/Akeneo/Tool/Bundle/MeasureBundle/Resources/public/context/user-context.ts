import {createContext} from 'react';

export type UserContextValue = (key: string) => string;

export const UserContext = createContext<UserContextValue>(() => '');
