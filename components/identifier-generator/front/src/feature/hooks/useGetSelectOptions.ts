import {Option, OptionCode} from '../models/option';
import {useQuery} from 'react-query';
import {useRouter} from '@akeneo-pim-community/shared';
import {ServerError, Unauthorized} from '../errors';
import {useEffect, useState} from 'react';

const DEFAULT_LIMIT_PAGINATION = 20;

type Props = {
  data?: Option[];
  isLoading: boolean;
  error: Error | null;
};

type Params = {
  attributeCode: string;
  page?: number;
  search?: string;
  codes?: OptionCode[];
};
const useGetSelectOptions = ({attributeCode, page = 1, search = '', codes}: Params): Props => {
  const router = useRouter();

  const {data, isLoading, error} = useQuery<Option[], Error, Option[]>({
    queryKey: ['getSelectOptions', page, search, codes],
    queryFn: async () => {
      const url = router.generate('akeneo_identifier_generator_get_attribute_options', {
        attributeCode,
        page: codes ? 1 : page,
        search,
        limit: codes ? codes.length : DEFAULT_LIMIT_PAGINATION,
        codes: codes || []
      });
      const response = await fetch(
        url,
        {
          method: 'GET',
          headers: [['X-Requested-With', 'XMLHttpRequest']],
        }
      );

      if (!response.ok) {
        if (response.status === 403) throw new Unauthorized();
        throw new ServerError();
      }

      return await response.json();
    },
  });

  return {data, isLoading, error};
};

type PaginationResult = {
  isLoading: boolean;
  options: Option[];
  handleNextPage: () => void;
  handleSearchChange: (value: string) => void;
  error: Error | null;
};

const usePaginatedOptions = (attributeCode: string): PaginationResult => {
  const [page, setPage] = useState<number>(1);
  const [hasNextPage, setHasNextPage] = useState(true);
  const [options, setOptions] = useState<Option[]>([]);
  const [search, setSearch] = useState('');
  const {data, isLoading, error} = useGetSelectOptions({attributeCode, page, search});

  useEffect(() => {
    if (!data) return;

    setHasNextPage(data.length === DEFAULT_LIMIT_PAGINATION);
    setOptions(formerOptions =>
      (page === 1 ? data : [...(formerOptions || []), ...data])
    );
  }, [data, page]);

  const handleNextPage = () => {
    if (!isLoading && hasNextPage) {
      setPage(prevPage => prevPage + 1);
    }
  };

  const handleSearchChange = (newSearch: string) => {
    if (newSearch !== search) {
      setSearch(newSearch);
      setPage(1);
      setHasNextPage(true);
    }
  };

  return {
    isLoading,
    options,
    handleNextPage,
    handleSearchChange,
    error,
  };
};

export {useGetSelectOptions, usePaginatedOptions};
