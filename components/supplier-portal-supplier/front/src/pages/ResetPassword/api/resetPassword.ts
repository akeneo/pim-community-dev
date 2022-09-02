import {apiFetch} from '../../../api/apiFetch';

const resetPassword = async (data: {email: string}) => {
    return await apiFetch(`/supplier-portal/authentication/contributor-account/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });
};

export {resetPassword};
