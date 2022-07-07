import {useEffect, useState} from 'react';

type User = {
    email: string;
};

const localStorageKey = 'supplier-portal-contributor-account';

const usePersistentUser = () => {
    const [user, updateUser] = useState<User | null>(null);
    const [isInitialized, setIsInitialized] = useState(false);

    useEffect(() => {
        const contributorAccount = localStorage.getItem(localStorageKey);
        if (null !== contributorAccount) {
            const user = JSON.parse(contributorAccount);
            null !== user && updateUser(user);
        }
        setIsInitialized(true);
    }, [updateUser]);

    useEffect(() => {
        if (null === user) {
            localStorage.removeItem(localStorageKey);
        } else {
            localStorage.setItem(localStorageKey, JSON.stringify(user));
        }
    }, [user]);

    return {
        user,
        updateUser,
        isInitialized,
    };
};

export {usePersistentUser};
export type {User};
