import {createContext} from 'react';

type UserContextValue = (key: string) => string;

const UserContext = createContext<UserContextValue>(() => '');

export {UserContextValue, UserContext};
