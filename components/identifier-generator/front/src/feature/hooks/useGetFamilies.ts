import {useEffect, useState} from 'react';
import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError, Unauthorized} from '../errors';
import {Family, FamilyCode} from '../models';

const DEFAULT_LIMIT_PAGINATION = 20;
type QueryKey = (string | number | FamilyCode[] | undefined)[];

const useGetFamilies =
  // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
  (params: {page?: number; search?: string; codes?: FamilyCode[]; limit?: number; enabled?: boolean}) => {
    const router = useRouter();

    return useQuery<Family[], Error, Family[], QueryKey>({
      queryKey: [
        'getFamilies',
        params.page ?? 1,
        params.search ?? '',
        params.codes,
        params.limit || DEFAULT_LIMIT_PAGINATION,
      ],
      queryFn: async (parameters: {queryKey: QueryKey}) => {
        const queryParameters: {[key: string]: string | number | FamilyCode[] | undefined} = {
          limit: params.limit || DEFAULT_LIMIT_PAGINATION,
          page: parameters.queryKey[1],
          search: parameters.queryKey[2],
        };
        if (typeof parameters.queryKey[3] !== 'undefined') {
          if ((parameters.queryKey[3] as FamilyCode[]).length === 0) {
            return [];
          }
          queryParameters.codes = parameters.queryKey[3] as FamilyCode[];
          queryParameters.page = 1;
          queryParameters.limit = queryParameters.codes.length;
        }
        const response = await fetch(router.generate('akeneo_identifier_generator_get_families', queryParameters), {
          method: 'GET',
          headers: [['X-Requested-With', 'XMLHttpRequest']],
        });

        if (!response.ok) {
          if (response.status === 403) throw new Unauthorized();
          throw new ServerError();
        }

        return await response.json();
      },
      enabled: params.enabled,
    });
  };

const usePaginatedFamilies: () => {
  families: Family[] | undefined;
  handleNextPage: () => void;
  handleSearchChange: (search: string) => void;
  error: Error | null;
} = () => {
  const [page, setPage] = useState<number>(1);
  const [hasNextPage, setHasNextPage] = useState<boolean>(true);
  const [families, setFamilies] = useState<Family[] | undefined>();
  const [search, setSearch] = useState<string>('');

  const {data, isLoading, error} = useGetFamilies({page, search});

  useEffect(() => {
    if (typeof data !== 'undefined') {
      if (data.length < DEFAULT_LIMIT_PAGINATION) {
        setHasNextPage(false);
      }
      setFamilies(formerFamilies => (page === 1 ? data : [...(formerFamilies || []), ...data]));
    }
  }, [data, page]);

  const handleNextPage = () => {
    if (!isLoading && hasNextPage) {
      setPage(page => page + 1);
    }
  };

  const handleSearchChange = (newSearch: string) => {
    if (newSearch !== search) {
      setSearch(newSearch);
      setPage(1);
      setHasNextPage(true);
    }
  };

  return {families, handleNextPage, handleSearchChange, error};
};

export {useGetFamilies, usePaginatedFamilies};
