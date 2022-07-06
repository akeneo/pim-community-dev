import {useInfiniteQuery} from 'react-query';
import {useDebounceCallback} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';

type Family = {
    label: string;
    code: string;
};
type PageParam = {
    number?: number;
    search?: string;
};
type Page = {
    data: Family[];
    page: PageParam;
};

type QueryParams = {
    search?: string;
    limit?: number;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Family[] | undefined;
    error: Error;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteFamilies = ({search = '', limit = 20}: QueryParams): Result => {
    const fetchFamilies = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const _search = search || pageParam?.search || '';

            const response = await fetch(`/rest/catalogs/families?page=${_page}&limit=${limit}&search=${_search}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            return {
                data: await response.json(),
                page: {
                    number: _page,
                    search: _search,
                },
            };
        },
        [search, limit]
    );

    const query = useInfiniteQuery<Page, Error, Page>(['families', {search: search, limit: limit}], fetchFamilies, {
        keepPreviousData: true,
        getNextPageParam: last =>
            last.data.length >= limit && last.page?.number
                ? {
                      number: last.page.number + 1,
                      search: search,
                  }
                : undefined,
    });

    const debouncedReset = useDebounceCallback(query.refetch, 300);

    if (query.data?.pages[0].page.search !== search && !query.isFetching) {
        debouncedReset();
    }

    return {
        isLoading: query.isLoading,
        isError: query.isError,
        data: query.data?.pages.reduce((list: Family[], page) => list.concat(page.data), []),
        error: query.error,
        fetchNextPage: async () => {
            if (!query.isFetching && !query.isLoading && query.hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
