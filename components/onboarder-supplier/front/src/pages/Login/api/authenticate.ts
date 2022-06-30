import {apiFetch} from '../../../api/apiFetch';

const authenticate = async (data: {email: string; password: string}): Promise<{email: string}> => {
    return await apiFetch('/onboarder-supplier/login', {
        method: 'POST',
        headers: [
            ['Content-type', 'application/json'],
            ['X-Requested-With', 'XMLHttpRequest'],
        ],
        body: JSON.stringify({username: data.email, password: data.password}),
    });
};

export {authenticate};
