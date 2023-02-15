import {useInfiniteQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError, Unauthorized} from '../errors';
import {useCallback, useMemo} from 'react';

type ItemsGroup = {
  id: string;
  text: string;
  children: {
    id: string;
    text: string;
  }[];
};

type PageParam = {
  number: number;
  search: string;
};

type Page = {
  data: ItemsGroup[];
  page: PageParam;
};

const LIMIT = 20;

type Response = {
  data?: ItemsGroup[];
  hasNextPage: boolean;
  fetchNextPage: () => void;
};

const useGetPropertyItems = (search: string, enabled: boolean): Response => {
  const router = useRouter();

  const fetchProperties = useCallback(
    async ({pageParam}: {pageParam?: PageParam}): Promise<Page> => {
      const _page = pageParam?.number || 1;
      const _search = search || pageParam?.search || '';
      const url = router.generate('akeneo_identifier_generator_get_properties', {
        search: _search,
        page: _page,
        limit: LIMIT,
        systemFields: ['free_text', 'auto_number', 'family'],
      });

      const response = await fetch(url, {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        if (response.status === 403) throw new Unauthorized();
        throw new ServerError();
      }

      return {
        data: await response.json(),
        page: {
          number: _page,
          search: _search,
        },
      };
    },
    [router, search]
  );

  const {data, isLoading, isFetching, hasNextPage, fetchNextPage} = useInfiniteQuery<Page, Error, Page>(
    ['getPropertyItems', search],
    fetchProperties,
    {
      enabled: enabled,
      keepPreviousData: true,
      getNextPageParam: last => {
        const total = last.data.map(value => value.children.length).reduce((acc, value) => (acc = acc + value));
        return total >= LIMIT
          ? {
              number: last.page.number + 1,
              search,
            }
          : undefined;
      },
    }
  );

  const readyForNextPage = useMemo(
    () => (!isFetching && !isLoading && hasNextPage) || false,
    [hasNextPage, isFetching, isLoading]
  );

  const reducedData = useMemo(
    () => data?.pages.reduce((list: ItemsGroup[], page) => list.concat(page.data), []),
    [data]
  );

  return {
    data: reducedData,
    hasNextPage: readyForNextPage,
    fetchNextPage: async () => {
      if (hasNextPage) {
        await fetchNextPage();
      }
    },
  };
};

export {useGetPropertyItems};
