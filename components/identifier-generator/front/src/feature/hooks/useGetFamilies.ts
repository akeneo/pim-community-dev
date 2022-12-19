import {useEffect, useState} from 'react';
import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError, Unauthorized} from '../errors';
import {Family, FamilyCode} from '../models';

const DEFAULT_LIMIT_PAGINATION = 20;
type QueryKey = (string | number | FamilyCode[] | undefined)[];

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useGetFamilies = (params: {page?: number; search?: string; codes?: FamilyCode[]}) => {
  const router = useRouter();

  return useQuery<Family[], Error, Family[], QueryKey>({
    queryKey: ['getFamilies', params.page ?? 1, params.search ?? '', params.codes],
    queryFn: async (parameters: {queryKey: QueryKey}) => {
      const queryParameters: {[key: string]: string | number | FamilyCode[] | undefined} = {
        limit: DEFAULT_LIMIT_PAGINATION,
        page: parameters.queryKey[1],
        search: parameters.queryKey[2],
      };
      if (typeof parameters.queryKey[3] !== 'undefined') {
        queryParameters.codes = parameters.queryKey[3] as FamilyCode[];
      }
      const response = await fetch(router.generate('akeneo_identifier_generator_get_families_list', queryParameters), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        if (response.status === 403) throw new Unauthorized();
        throw new ServerError();
      }

      return await response.json();
    },
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
  const [hasSearchChanged, setHasSearchChanged] = useState<boolean>(false);

  const {data, isLoading, error} = useGetFamilies({page, search});

  useEffect(() => {
    if (typeof data !== 'undefined') {
      if (data.length < DEFAULT_LIMIT_PAGINATION) {
        setHasNextPage(false);
      }
      setFamilies(formerFamilies => (hasSearchChanged ? data : [...(formerFamilies || []), ...data]));
    }
  }, [data, hasSearchChanged]);

  const handleNextPage = () => {
    if (!isLoading && hasNextPage) {
      setHasSearchChanged(false);
      setPage(page => page + 1);
    }
  };

  const handleSearchChange = (newSearch: string) => {
    if (newSearch !== search) {
      setHasSearchChanged(true);
      setSearch(newSearch);
      setPage(1);
      setHasNextPage(true);
    }
  };

  return {families, handleNextPage, handleSearchChange, error};
};

export {useGetFamilies, usePaginatedFamilies};
