import {useInfiniteQuery} from 'react-query';
import {useCallback} from 'react';
import {AttributeOption} from '../models/AttributeOption';

type PageParam = {
    number: number;
    search: string;
};
type Page = {
    data: AttributeOption[];
    page: PageParam;
};

type QueryParams = {
    attribute: string;
    locale?: string;
    search?: string;
    codes?: string[];
    limit?: number;
    enabled?: boolean;
};
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: AttributeOption[] | undefined;
    error: Error | null;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteAttributeOptions = ({
    attribute,
    locale = 'en_US',
    search = '',
    codes = [],
    limit = 20,
    enabled = true,
}: QueryParams): Result => {
    const fetchAttributeOptions = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const _search = search || pageParam?.search || '';
            const _codes = codes.join(',');

            const params = new URLSearchParams({
                locale: locale,
                codes: _codes,
                search: _search,
                page: _page.toString(),
                limit: limit.toString(),
            }).toString();

            const response = await fetch(`/rest/catalogs/attributes/${attribute}/options?` + params, {
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
        [attribute, locale, search, codes, limit]
    );

    const query = useInfiniteQuery<Page, Error | null, Page>(
        ['attribute_options', {attribute: attribute, search: search, locale: locale, codes: codes, limit: limit}],
        fetchAttributeOptions,
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
        data: query.data?.pages.reduce((list: AttributeOption[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
