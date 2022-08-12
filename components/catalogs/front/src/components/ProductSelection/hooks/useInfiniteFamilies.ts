import {useInfiniteQuery} from 'react-query';
import {useCallback} from 'react';
import {Family} from '../models/Family';
import {getFamilies} from '../../../api/getFamilies';
import {getFamiliesByCode} from '../../../api/getFamiliesByCode';

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

export const useInfiniteFamilies = ({search = '', codes = [], limit = 20}: QueryParams = {}): Result => {
    const fetchFamilies = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const page = {
                number: pageParam?.number || 1,
                search: search || pageParam?.search || '',
            };

            const families =
                codes?.length > 0
                    ? await getFamiliesByCode(page.number, limit, codes)
                    : await getFamilies(page.number, limit, page.search);

            return {
                data: families,
                page: page,
            };
        },
        [search, codes, limit]
    );

    const query = useInfiniteQuery<Page, Error, Page>(
        ['families', {search: search, codes: codes, limit: limit}],
        fetchFamilies,
        {
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
