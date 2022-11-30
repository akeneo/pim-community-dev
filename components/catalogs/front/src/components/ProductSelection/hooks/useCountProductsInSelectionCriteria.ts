import {useQuery} from 'react-query';
import {ProductSelectionValues} from '../models/ProductSelectionValues';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: number | null | undefined;
    error: ResultError;
};

export const useCountProductsInSelectionCriteria = (productSelectionCriteria: ProductSelectionValues): Result => {
    const criteria = JSON.stringify(productSelectionCriteria);
    return useQuery<number, ResultError, number>(['productSelectionCriteria', criteria], async () => {
        const response = await fetch('/rest/catalogs/product-selection-criteria/product/count', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: criteria,
        });

        return await response.json();
    });
};
