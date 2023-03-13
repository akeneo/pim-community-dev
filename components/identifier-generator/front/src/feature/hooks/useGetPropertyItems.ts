import {useInfiniteQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError} from '../errors';
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
  //hasNextPage: boolean;
  fetchNextPage: () => void;
  error: Error | null;
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

  const {data, hasNextPage, fetchNextPage, error} = useInfiniteQuery<Page, Error, Page>(
    ['getPropertyItems', search],
    fetchProperties,
    {
      enabled: enabled,
      keepPreviousData: true,
      getNextPageParam: last => {
        const total = last.data?.map(value => value.children.length).reduce((acc, value) => (acc = acc + value), 0);
        return total >= LIMIT
          ? {
              number: last.page.number + 1,
              search,
            }
          : undefined;
      },
    }
  );

  const reducedData = useMemo(
    () => data?.pages.reduce((list: ItemsGroup[], page) => list.concat(page.data), []),
    [data]
  );

  return {
    data: reducedData,
    fetchNextPage: async () => {
      if (hasNextPage) {
        await fetchNextPage();
      }
    },
    error,
  };
};

export {useGetPropertyItems};
