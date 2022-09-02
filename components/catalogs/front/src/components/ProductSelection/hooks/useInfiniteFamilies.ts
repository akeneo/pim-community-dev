import {useInfiniteQuery} from 'react-query';
import {useCallback} from 'react';
import {Family} from '../models/Family';

type PageParam = {
    number: number;
    search: string;
};
type Page = {
    data: Family[];
    page: PageParam;
};

type QueryParams = {
    search?: string;
    codes?: string[];
    limit?: number;
    enabled?: boolean;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Family[] | undefined;
    error: Error;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteFamilies = ({
    search = '',
    codes = [],
    limit = 20,
    enabled = true,
}: QueryParams = {}): Result => {
    const fetchFamilies = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const _search = search || pageParam?.search || '';
            const _codes = codes.join(',');

            const response = await fetch(
                `/rest/catalogs/families?page=${_page}&limit=${limit}&codes=${_codes}&search=${_search}`,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            return {
                data: await response.json(),
                page: {
                    number: _page,
                    search: _search,
                },
            };
        },
        [search, codes, limit]
    );

    const query = useInfiniteQuery<Page, Error, Page>(
        ['families', {search: search, codes: codes, limit: limit}],
        fetchFamilies,
        {
            enabled: enabled,
            keepPreviousData: true,
            getNextPageParam: last =>
                last.data.length >= limit
                    ? {
                          number: last.page.number + 1,
                          search: search,
                      }
                    : undefined,
        }
    );

    const hasNextPage = (!query.isFetching && !query.isLoading && query.hasNextPage) || false;

    return {
        isLoading: query.isLoading,
        isError: query.isError,
        data: query.data?.pages.reduce((list: Family[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
