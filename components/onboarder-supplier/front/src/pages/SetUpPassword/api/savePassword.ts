import {apiFetch} from '../../../api/apiFetch';

const savePassword = async (data: {contributorAccountIdentifier: string; plainTextPassword: string}) => {
    return await apiFetch(`/onboarder-supplier/authentication/set-up-password`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });
};

export {savePassword};
