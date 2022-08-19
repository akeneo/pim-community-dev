import {useQuery, useQueryClient} from 'react-query';
import {Category, CategoryCode} from '../models/Category';
import {useEffect} from 'react';
import {useUserContext} from '@akeneo-pim-community/shared';

type Data = Category[];
type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Data | undefined;
    error: ResultError;
};

export const useCategoryChildren = (categoryCode: CategoryCode): Result => {
    const locale = useUserContext().get('uiLocale');
    const queryClient = useQueryClient();
    const queryResult = useQuery<Data, ResultError, Data>(['category-children', categoryCode], async () => {
        const response = await fetch(`/rest/catalogs/categories/${categoryCode}/children?locale=${locale}`, {
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

    const categoryChildren = queryResult.data;
    useEffect(() => {
        if (categoryChildren === undefined) {
            return;
        }
        categoryChildren.forEach(child => {
            queryClient.setQueryData(['categories', {codes: [child.code], isRoot: false}], [child]);
        });
    }, [categoryChildren, queryClient]);

    return queryResult;
};
