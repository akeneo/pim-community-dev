import {useQuery} from 'react-query';
import {Category, CategoryCode} from '../models/Category';
import {useCallback} from 'react';

type Data = Category[];
type ResultError = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCategories = (codes: CategoryCode[]): Result => {
    const fetchCategories = useCallback(async () => {
        const _codes = codes.join(',');
        const response = await fetch(`/rest/catalogs/categories?codes=${_codes}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    }, [codes]);

    return useQuery<Data, ResultError, Data>(['categories', codes], fetchCategories);
};
