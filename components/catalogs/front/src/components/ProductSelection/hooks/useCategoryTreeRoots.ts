import {useQueryClient} from 'react-query';
import {Category} from '../models/Category';
import {useCategories} from './useCategories';
import {useEffect} from 'react';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Category[] | undefined;
    error: ResultError;
};

export const useCategoryTreeRoots = (): Result => {
    const queryClient = useQueryClient();
    const categoryResult = useCategories({isRoot: true});

    const categoryTreeRoots = categoryResult.data;

    useEffect(() => {
        if (categoryTreeRoots === undefined) {
            return;
        }
        categoryTreeRoots.forEach(categoryTreeRoot => {
            queryClient.setQueryData(
                ['categories', {codes: [categoryTreeRoot.code], isRoot: false}],
                [categoryTreeRoot]
            );
        });
    }, [categoryTreeRoots, queryClient]);

    return categoryResult;
};
