import {useCallback} from 'react';
import {CriterionFactory} from '../models/CriterionFactory';
import {useInfiniteQuery} from 'react-query';
import {Attribute} from '../../../models/Attribute';
import {useFindAttributeCriterionByType} from './useFindAttributeCriterionByType';

type PageParam = {
    number: number;
    search: string;
};
type Page = {
    data: CriterionFactory[];
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
    data: CriterionFactory[] | undefined;
    error: Error;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteAttributeCriterionFactories = ({search = '', limit = 20}: QueryParams = {}): Result => {
    const findAttributeCriterionByType = useFindAttributeCriterionByType();

    const fetchAttributes = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const _search = search || pageParam?.search || '';

            const response = await fetch(`/rest/catalogs/attributes?page=${_page}&limit=${limit}&search=${_search}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const attributes: Attribute[] = await response.json();
            const factories: CriterionFactory[] = attributes.map(attribute => ({
                id: attribute.code,
                label: attribute.label,
                factory: () => findAttributeCriterionByType(attribute.type).factory({field: attribute.code}),
            }));

            return {
                data: factories,
                page: {
                    number: _page,
                    search: _search,
                },
            };
        },
        [search, limit, findAttributeCriterionByType]
    );

    const query = useInfiniteQuery<Page, Error, Page>(['attributes', {search: search, limit: limit}], fetchAttributes, {
        keepPreviousData: true,
        getNextPageParam: last =>
            last.data.length >= limit
                ? {
                      number: last.page.number + 1,
                      search: search,
                  }
                : undefined,
    });

    const hasNextPage = (!query.isFetching && !query.isLoading && query.hasNextPage) || false;

    return {
        isLoading: query.isLoading,
        isError: query.isError,
        data: query.data?.pages.reduce((list: CriterionFactory[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
