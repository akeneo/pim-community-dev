import {useInfiniteQuery} from 'react-query';
import {useCallback} from 'react';
import {Channel} from '../models/Channel';

type PageParam = {
    number: number;
};
type Page = {
    data: Channel[];
    page: PageParam;
};

type QueryParams = {
    limit?: number;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Channel[] | undefined;
    error: Error;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteChannels = ({limit = 20}: QueryParams = {}): Result => {
    const fetchChannels = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const response = await fetch(`/rest/catalogs/channels?page=${_page}&limit=${limit}`, {
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

    const query = useInfiniteQuery<Page, Error, Page>(['channels', {limit: limit}], fetchChannels, {
        keepPreviousData: true,
        getNextPageParam: last => (last.data.length >= limit ? {number: last.page.number + 1} : undefined),
    });

    const hasNextPage = (!query.isFetching && !query.isLoading && query.hasNextPage) || false;

    return {
        isLoading: query.isLoading,
        isError: query.isError,
        data: query.data?.pages.reduce((list: Channel[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
