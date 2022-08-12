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

export const useCategoryTreeRoots = (): Result => {
    const queryClient = useQueryClient();
    return useQuery<Data, ResultError, Data>(['category-tree-roots'], async () => {
        const response = await fetch('/rest/catalogs/categories/tree-roots', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const responseJson = await response.json();

        if (response.ok) {
            const categoryTreeRoots = responseJson as Category[];
            categoryTreeRoots.forEach(categoryTreeRoot => {
                queryClient.setQueryData(['categories', [categoryTreeRoot.code]], [categoryTreeRoot]);
            });
        }

        return responseJson;
    });
};
