import {useInfiniteQuery} from 'react-query';
import {useCallback} from 'react';
import {Locale} from '../models/Locale';

type PageParam = {
    number: number;
};
type Page = {
    data: Locale[];
    page: PageParam;
};

type QueryParams = {
    limit?: number;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Locale[] | undefined;
    error: Error;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteLocales = ({limit = 20}: QueryParams = {}): Result => {
    const fetchLocales = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const response = await fetch(`/rest/catalogs/locales?page=${_page}&limit=${limit}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return {
                data: await response.json(),
                page: {number: _page},
            };
        },
        [limit]
    );

    const query = useInfiniteQuery<Page, Error, Page>(['locales', {limit: limit}], fetchLocales, {
        keepPreviousData: true,
        getNextPageParam: last => (last.data.length >= limit ? {number: last.page.number + 1} : undefined),
    });

    const hasNextPage = (!query.isFetching && !query.isLoading && query.hasNextPage) || false;

    return {
        isLoading: query.isLoading,
        isError: query.isError,
        data: query.data?.pages.reduce((list: Locale[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
