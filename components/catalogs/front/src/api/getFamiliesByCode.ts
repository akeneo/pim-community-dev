import {Family} from '../models/Family';

export const getFamiliesByCode = async (page = 1, limit = 20, codes: string[] = []): Promise<Family[]> => {
    const response = await fetch(`/rest/catalogs/families?page=${page}&limit=${limit}&codes=${codes.join(',')}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`${response.status} ${response.statusText}`);
    }

    return await response.json();
};
