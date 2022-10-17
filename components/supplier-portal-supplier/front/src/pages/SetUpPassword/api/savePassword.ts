import {apiFetch} from '../../../api/apiFetch';

const savePassword = async (data: {
    contributorAccountIdentifier: string;
    plainTextPassword: string;
    consent: boolean;
}) => {
    return await apiFetch(`/supplier-portal/authentication/set-up-password`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });
};

export {savePassword};
