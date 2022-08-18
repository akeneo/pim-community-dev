import {useQuery} from 'react-query';
import {Category, CategoryCode} from '../models/Category';

type QueryParams = {
    codes?: CategoryCode[];
    isRoot?: boolean;
};
type Data = Category[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCategories = ({codes = [], isRoot = false}: QueryParams): Result => {
    return useQuery<Data, ResultError, Data>(['categories', {codes, isRoot}], async () => {
        if (isRoot && codes.length > 0) {
            throw new Error('Cannot use codes and root simultaneously to fetch categories');
        }

        if (!isRoot && codes.length === 0) {
            return [];
        }

        const queryParameters = new URLSearchParams({
            codes: codes.join(','),
            is_root: isRoot ? '1' : '0',
        }).toString();

        const response = await fetch('/rest/catalogs/categories?' + queryParameters, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        return await response.json();
    });
};
