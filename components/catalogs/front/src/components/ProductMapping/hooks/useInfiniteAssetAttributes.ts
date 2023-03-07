import {useCallback} from 'react';
import {useInfiniteQuery, useQueryClient} from 'react-query';
import {AssetAttribute} from '../models/AssetAttribute';
import {Target} from '../models/Target';

type PageParam = {
    number: number;
    search: string;
};
type Page = {
    data: AssetAttribute[];
    page: PageParam;
};
type QueryParams = {
    target: Target;
    assetFamilyCode: string;
    search?: string;
    limit?: number;
};
type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: AssetAttribute[] | undefined;
    error: Error;
    hasNextPage: boolean;
    fetchNextPage: () => Promise<void>;
};

export const useInfiniteAssetAttributes = ({target, assetFamilyCode, search = '', limit = 20}: QueryParams): Result => {
    const queryClient = useQueryClient();

    const fetchAssetAttributes = useCallback(
        async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
            const _page = pageParam?.number || 1;
            const _search = search || pageParam?.search || '';
            const queryParameters = new URLSearchParams({
                page: _page.toString(),
                limit: limit.toString(),
                search: _search,
                targetType: target.type,
                targetFormat: target.format || '',
                assetFamily: assetFamilyCode,
            }).toString();

            const response = await fetch(
                '/rest/catalogs/asset-attributes-by-target-type-and-target-format?' + queryParameters,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            const assetAttributes: AssetAttribute[] = await response.json();

            Object.entries(assetAttributes).forEach(([, assetAttribute]) =>
                queryClient.setQueryData(['assetAttribute', assetAttribute.identifier], assetAttribute)
            );

            return {
                data: assetAttributes,
                page: {
                    number: _page,
                    search: _search,
                },
            };
        },
        [search, limit, queryClient, target.format, target.type, assetFamilyCode]
    );

    const query = useInfiniteQuery<Page, Error, Page>(
        ['assetAttributes', {search: search, limit: limit, type: target.type, targetFormat: target.format || '', assetFamilyCode: assetFamilyCode}],
        fetchAssetAttributes,
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
        data: query.data?.pages.reduce((list: AssetAttribute[], page) => list.concat(page.data), []),
        error: query.error,
        hasNextPage: hasNextPage,
        fetchNextPage: async () => {
            if (hasNextPage) {
                await query.fetchNextPage();
            }
        },
    };
};
