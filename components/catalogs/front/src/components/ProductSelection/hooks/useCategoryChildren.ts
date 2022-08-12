import {useQuery, useQueryClient} from 'react-query';
import {Category} from '../models/Category';

type Data = Category[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCategoryChildren = (categoryId: number): Result => {
    const queryClient = useQueryClient();
    return useQuery<Data, ResultError, Data>(['category-children', categoryId], async () => {
        const response = await fetch(`/rest/catalogs/categories/${categoryId}/children`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseJson = await response.json();

        if (response.ok) {
            const children = responseJson as Category[];
            children.forEach(child => {
                queryClient.setQueryData(['categories', [child.code]], [child]);
            });
        }

        return responseJson;
    });
};
