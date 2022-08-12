import {Catalog} from '../models/Catalog';

export const getCatalog = async (id: string): Promise<Catalog> => {
    const response = await fetch('/rest/catalogs/' + id, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`${response.status} ${response.statusText}`);
    }

    return await response.json();
};
