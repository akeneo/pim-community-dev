import React, {createContext, ReactElement, useContext} from 'react';
import {usePersistentUser, User} from './usePersistentUser';

type UserContextType = {
    user: User | null;
    updateUser: (user: User | null) => void;
    isAuthenticated: boolean;
};

const UserContext = createContext<UserContextType>({user: null, updateUser: () => {}, isAuthenticated: false});

const UserContextProvider = ({children}: {children: ReactElement}) => {
    const {user, updateUser, isInitialized} = usePersistentUser();

    if (!isInitialized) {
        return null;
    }

    const state: UserContextType = {
        user,
        updateUser,
        isAuthenticated: null !== user,
    };

    return <UserContext.Provider value={state}>{children}</UserContext.Provider>;
};

const useUserContext = () => {
    return useContext(UserContext);
};

export {UserContextProvider, UserContext, useUserContext};
