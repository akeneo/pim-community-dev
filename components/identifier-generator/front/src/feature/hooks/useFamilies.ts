import React, {useEffect} from 'react';
import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError} from '../errors';
import {Family} from '../models';

const DEFAULT_LIMIT_PAGINATION = 20;

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
const useGetFamilies = (page: number, search: string) => {
  const router = useRouter();

  return useQuery<Family[], Error, Family[], (string|number)[]>({
    queryKey: ['getFamilies', page, search],
    queryFn: async (parameters: {queryKey: (string|number)[]}) => {
      console.log(parameters);
      const response = await fetch(router.generate('akeneo_identifier_generator_get_families_list', {
        limit: DEFAULT_LIMIT_PAGINATION,
        page: parameters.queryKey[1],
        search: parameters.queryKey[2],
      }), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });

      if (!response.ok) {
        //if (response.status === 404) throw new IdentifierGeneratorNotFound();
        throw new ServerError();
      }

      return await response.json();
    }
  });
};

const usePaginatedFamilies = () => {
  const [page, setPage] = React.useState<number>(1);
  const [arriveAuBout, setArriveAuBout] = React.useState<boolean>(false);
  const [families, setFamilies] = React.useState<Family[] | undefined>();
  const [search, setSearch] = React.useState<string>('');
  const [shouldCleanFamilies, setShouldCleanFamilies] = React.useState<boolean>(false);

  const {data, isLoading} = useGetFamilies(page, search);

  useEffect(() => {
    if (typeof data !== 'undefined') {
      if (data.length < DEFAULT_LIMIT_PAGINATION) {
        setArriveAuBout(true);
      }
      setFamilies(oldFamily => shouldCleanFamilies ? data : ([...(oldFamily || []), ...data]));
    }
  }, [data]);

  const handleNextPage = () => {
    if (!isLoading && !arriveAuBout) {
      setPage((page) => page + 1);
    }
  }

  const handleSearchChange = (s: string) => {
    setSearch(s);
    setPage(1);
    setShouldCleanFamilies(true);
  }

  return {families, handleNextPage, handleSearchChange}
}

export {useGetFamilies, usePaginatedFamilies};
