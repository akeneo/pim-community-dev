import {useQuery} from 'react-query';
import {ProductSelectionValues} from '../ProductSelection';

export type Product = {
    uuid: string,
    name: string,
};

type Data = Product[];
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useAffectedProductsQuery = (
    catalogId: string,
    productSelectionCriteria: ProductSelectionValues,
    search: string
): Result => {
    const criteria = JSON.stringify(productSelectionCriteria);
    return useQuery<Data, Error, Data>(['products-preselected', catalogId, search, criteria], async () => {
        const queryParameters = new URLSearchParams({search}).toString();
        const response = await fetch(`/rest/catalogs/${catalogId}/products/preselected?` + queryParameters, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            method: 'POST',
            body: criteria,
        });

        return await response.json();
    });
};
