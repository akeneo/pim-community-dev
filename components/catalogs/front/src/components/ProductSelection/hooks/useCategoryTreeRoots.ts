import {useQueryClient} from 'react-query';
import {Category} from '../models/Category';
import {useCategories} from './useCategories';
import {useEffect} from 'react';
import {useUserContext} from '@akeneo-pim-community/shared';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Category[] | undefined;
    error: ResultError;
};

export const useCategoryTreeRoots = (): Result => {
    const locale = useUserContext().get('catalogLocale');
    const queryClient = useQueryClient();
    const {data: categoryTreeRoots, isLoading, isError, error} = useCategories({isRoot: true});

    useEffect(() => {
        if (categoryTreeRoots === undefined) {
            return;
        }
        categoryTreeRoots.forEach(categoryTreeRoot => {
            queryClient.setQueryData(
                ['categories', {codes: [categoryTreeRoot.code], isRoot: false, locale}],
                [categoryTreeRoot]
            );
        });
    }, [categoryTreeRoots, queryClient, locale]);

    return {
        isLoading,
        isError,
        error,
        data: categoryTreeRoots,
    };
};
