import React, {createContext, useContext, useState, ReactElement} from 'react';

type User = {
    email: string;
};

type ContextType = {
    user: User | null;
    updateUser: (user: User | null) => void;
    isAuthenticated: boolean;
};

const UserContext = createContext<ContextType>({user: null, updateUser: () => {}, isAuthenticated: false});

const UserContextProvider = ({children}: {children: ReactElement}) => {
    const [user, updateUser] = useState<User | null>(null);

    const state: ContextType = {
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
