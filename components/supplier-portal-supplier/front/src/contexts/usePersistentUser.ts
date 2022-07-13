import {useEffect, useState} from 'react';
import {apiFetch} from '../api/apiFetch';
import {UnauthorizedError} from '../api';

type User = {
    email: string;
};

const localStorageKey = 'supplier-portal-contributor-account';

const usePersistentUser = () => {
    const [user, updateUser] = useState<User | null>(null);
    const [isInitialized, setIsInitialized] = useState(false);

    useEffect(() => {
        (async () => {
            const contributorAccount = localStorage.getItem(localStorageKey);
            if (null !== contributorAccount) {
                const user = JSON.parse(contributorAccount);
                if (null !== user) {
                    try {
                        await apiFetch(`/supplier-portal/check-authentication`, {
                            method: 'POST',
                            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                            body: `email=${user.email}`,
                        });
                        updateUser(user);
                    } catch (error) {
                        if (error instanceof UnauthorizedError) {
                            localStorage.removeItem(localStorageKey);
                            updateUser(null);
                        }
                    }
                }
            }
            setIsInitialized(true);
        })();
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
