import {Family} from '../models/Family';

export const getFamilies = async (page = 1, limit = 20, search = ''): Promise<Family[]> => {
    const response = await fetch(`/rest/catalogs/families?page=${page}&limit=${limit}&search=${search}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`${response.status} ${response.statusText}`);
    }

    return await response.json();
};
