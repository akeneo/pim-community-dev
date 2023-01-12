import {useCallback} from 'react';
import {useInfiniteQuery, useQueryClient} from 'react-query';
import {Attribute} from '../../../models/Attribute';

type PageParam = {
    number: number;
    search: string;
};
type Page = {
    data: Attribute[];
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
    data: Attribute[] | undefined;
    error: Error;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

const ALLOWED_ATTRIBUTE_TYPES = ['text', 'textarea', 'simpleselect'];

export const useInfiniteAttributes = ({search = '', limit = 20}: QueryParams = {}): Result => {
    const queryClient = useQueryClient();

    const fetchAttributes = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const _search = search || pageParam?.search || '';

            const queryParameters = new URLSearchParams({
                page: _page.toString(),
                limit: limit.toString(),
                search: _search,
                types: ALLOWED_ATTRIBUTE_TYPES.join(','),
            }).toString();

            const response = await fetch('/rest/catalogs/attributes?' + queryParameters, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const attributes: Attribute[] = await response.json();

            Object.entries(attributes).forEach(([, attribute]) =>
                queryClient.setQueryData(['attribute', attribute.code], attribute)
            );

            return {
                data: attributes,
                page: {
                    number: _page,
                    search: _search,
                },
            };
        },
        [search, limit, queryClient]
    );

    const query = useInfiniteQuery<Page, Error, Page>(
        ['attributes', {search: search, limit: limit, types: ALLOWED_ATTRIBUTE_TYPES}],
        fetchAttributes,
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
        data: query.data?.pages.reduce((list: Attribute[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
