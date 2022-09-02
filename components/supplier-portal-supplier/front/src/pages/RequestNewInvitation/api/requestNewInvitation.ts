import {apiFetch} from '../../../api/apiFetch';

const requestNewInvitation = async (data: {email: string}) => {
    return await apiFetch(`/supplier-portal/authentication/contributor-account/request-new-invitation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });
};

export {requestNewInvitation};
