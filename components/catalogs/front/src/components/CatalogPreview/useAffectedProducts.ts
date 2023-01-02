import {useQuery} from 'react-query';
import {ProductSelectionValues} from '../ProductSelection';

type Data = {

};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: Error;
};

export const useAffectedProductsQuery = (productSelectionCriteria: ProductSelectionValues, search: string): Result => {
    const criteria = JSON.stringify(productSelectionCriteria);
    console.log(criteria);
    return useQuery<Data, Error, Data>(['products-preview', search, criteria], async () => {
        const queryParameters = new URLSearchParams({
            productSelectionCriteria: criteria,
            search
        }).toString();
        const response = await fetch('/rest/catalogs/products/' + queryParameters, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
