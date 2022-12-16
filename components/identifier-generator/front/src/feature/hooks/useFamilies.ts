import React, {useEffect} from 'react';
import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {IdentifierGeneratorNotFound, ServerError, Unauthorized} from '../errors';
import {Family, FamilyCode} from '../models';

const DEFAULT_LIMIT_PAGINATION = 20;
type QueryKey = (string|number|FamilyCode[]|undefined)[];

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useGetFamilies = (params: {page?: number, search?: string, codes?: FamilyCode[]}) => {
  const router = useRouter();

  return useQuery<Family[], Error, Family[], QueryKey>({
    queryKey: ['getFamilies', params.page ?? 1, params.search ?? '', params.codes],
    queryFn: async (parameters: {queryKey: QueryKey}) => {
      const queryParameters: {[key:string]: any} = {
        limit: DEFAULT_LIMIT_PAGINATION,
        page: parameters.queryKey[1],
        search: parameters.queryKey[2],
      };
      if (typeof parameters.queryKey[3] !== 'undefined') {
        queryParameters.codes = (parameters.queryKey[3] as FamilyCode[]).join(',');
      }
      const response = await fetch(router.generate(
        'akeneo_identifier_generator_get_families_list',
        queryParameters
      ), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        if (response.status === 403) throw new Unauthorized();
        throw new ServerError();
      }

      return await response.json();
    }
  });
};

const usePaginatedFamilies = () => {
  const [page, setPage] = React.useState<number>(1);
  const [hasNextPage, setHasNextPage] = React.useState<boolean>(true);
  const [families, setFamilies] = React.useState<Family[] | undefined>();
  const [search, setSearch] = React.useState<string>('');
  const [hasSearchChanged, setHasSearchChanged] = React.useState<boolean>(false);

  const {data, isLoading} = useGetFamilies({page, search});

  useEffect(() => {
    if (typeof data !== 'undefined') {
      if (data.length < DEFAULT_LIMIT_PAGINATION) {
        setHasNextPage(false);
      }
      setFamilies(oldFamily => hasSearchChanged ? data : ([...(oldFamily || []), ...data]));
    }
  }, [data]);

  const handleNextPage = () => {
    if (!isLoading && hasNextPage) {
      setPage((page) => page + 1);
    }
  }

  const handleSearchChange = (newSearch: string) => {
    if (newSearch !== search) {
      setHasSearchChanged(true);
      setSearch(newSearch);
      setPage(1);
      setHasNextPage(true);
    }
  }

  return {families, handleNextPage, handleSearchChange}
}

export {useGetFamilies, usePaginatedFamilies};
