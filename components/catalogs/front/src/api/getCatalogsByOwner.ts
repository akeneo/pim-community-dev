import {Catalog} from '../models/Catalog';

export const getCatalogsByOwner = async (owner: string): Promise<Catalog[]> => {
    const response = await fetch('/rest/catalogs?owner=' + owner, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`${response.status} ${response.statusText}`);
    }

    return await response.json();
};
