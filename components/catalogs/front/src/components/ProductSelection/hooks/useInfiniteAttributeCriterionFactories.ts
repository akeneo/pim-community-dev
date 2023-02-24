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

const ALLOWED_ATTRIBUTE_TYPES = [
    'pim_catalog_identifier',
    'pim_catalog_text',
    'pim_catalog_textarea',
    'pim_catalog_simpleselect',
    'pim_catalog_multiselect',
    'pim_catalog_number',
    'pim_catalog_metric',
    'pim_catalog_boolean',
    'pim_catalog_date',
];

export const useInfiniteAttributeCriterionFactories = ({search = '', limit = 20}: QueryParams = {}): Result => {
    const findAttributeCriterionByType = useFindAttributeCriterionByType();

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
            const factories: CriterionFactory[] = attributes.map(attribute => ({
                id: attribute.code,
                label: attribute.label,
                group_code: attribute.attribute_group_code,
                group_label: attribute.attribute_group_label,
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
